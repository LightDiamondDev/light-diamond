<?php

namespace App\Services\Material;

use App\Models\Enums\MaterialSubmissionActionType;
use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use App\Models\Material;
use App\Models\MaterialFile;
use App\Models\MaterialFileState;
use App\Models\MaterialState;
use App\Models\MaterialSubmission;
use App\Models\MaterialVersion;
use App\Models\MaterialVersionState;
use App\Models\User;
use App\Registries\CategoryRegistry;
use App\Services\Material\Dto\MaterialSubmissionActionDto;
use App\Services\Material\Dto\MaterialSubmissionCreateDto;
use App\Services\Material\Dto\MaterialSubmissionUpdateDto;
use App\Services\MaterialVersion\MaterialVersionService;
use App\Services\MaterialVersion\MaterialVersionSubmissionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

readonly class MaterialSubmissionService
{
    public function __construct(
        private MaterialSubmissionActionService  $actionService,
        private MaterialVersionService           $versionService,
        private MaterialVersionSubmissionService $versionSubmissionService,
        private MaterialService                  $materialService,
        private MaterialStateService             $materialStateService,
        private CategoryRegistry                 $categoryRegistry
    )
    {
    }

    public function assignModerator(MaterialSubmission $materialSubmission, User $moderator): void
    {
        Model::withoutTimestamps(fn() => $materialSubmission->assignedModerator()->associate($moderator)->save());

        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(
                type: MaterialSubmissionActionType::AssignModerator,
                details: ['moderator_id' => $moderator->id]
            ),
        );
    }

    public function message(MaterialSubmission $materialSubmission, string $message): void
    {
        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(
                type: MaterialSubmissionActionType::Message,
                details: [
                    'message' => $message,
                    'is_moderator' => Auth::user()->is_moderator,
                ]
            ),
        );
    }

    public function createDraft(MaterialSubmissionCreateDto $dto): MaterialSubmission
    {
        return $this->create($dto, MaterialSubmissionStatus::Draft);
    }

    public function submitNew(MaterialSubmissionCreateDto $dto): MaterialSubmission
    {
        $materialSubmission = $this->create($dto, MaterialSubmissionStatus::Pending);

        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(MaterialSubmissionActionType::Submit)
        );

        return $materialSubmission;
    }

    public function submit(MaterialSubmission $materialSubmission, MaterialSubmissionUpdateDto $dto): void
    {
        $this->update($materialSubmission, $dto, MaterialSubmissionStatus::Pending);

        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(MaterialSubmissionActionType::Submit)
        );
    }

    public function requestChanges(MaterialSubmission $materialSubmission, MaterialSubmissionUpdateDto $dto): void
    {
        $materialSubmission->assignedModerator()->associate(Auth::user());
        $this->update($materialSubmission, $dto, MaterialSubmissionStatus::Draft);

        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(
                type: MaterialSubmissionActionType::RequestChanges,
                details: $dto->actionDetails
            ),
        );
    }

    public function accept(MaterialSubmission $materialSubmission, MaterialSubmissionUpdateDto $dto): void
    {
        $now = Carbon::now();

        $materialSubmission->assignedModerator()->associate(Auth::user());
        $this->update($materialSubmission, $dto, MaterialSubmissionStatus::Accepted, $now);

        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(MaterialSubmissionActionType::Accept)
        );

        $processSubmittable      = function (Material|MaterialFile|MaterialVersion $submittable, SubmissionType $submissionType) use ($now) {
            switch ($submissionType) {
                case SubmissionType::Create:
                    $submittable->published_at = $now;
                    $submittable->save();
                    break;
                case SubmissionType::Update:
                    break;
                case SubmissionType::Delete:
                    $submittable->delete();
                    break;
            }
        };
        $processSubmittableState = function (MaterialState|MaterialFileState|MaterialVersionState|null $state) use ($now) {
            if ($state !== null) {
                $state->published_at = $now;
                $state->save();
            }
        };

        $material = $materialSubmission->material;
        $processSubmittable($material, $materialSubmission->type);
        $processSubmittableState($materialSubmission->materialState);

        $category = $this->categoryRegistry->get($material->category);

        if ($category->isDownloadable()) {
            foreach ($materialSubmission->versionSubmissions as $versionSubmission) {
                $processSubmittable($versionSubmission->version, $versionSubmission->type);
                $processSubmittableState($versionSubmission->versionState);

                foreach ($versionSubmission->fileSubmissions as $fileSubmission) {
                    $processSubmittable($fileSubmission->file, $fileSubmission->type);
                    $processSubmittableState($fileSubmission->fileState);
                }
            }
        } else {
            if ($materialSubmission->type === SubmissionType::Create) {
                $this->versionService->create($material, $now);
            }
        }
    }

    public function reject(MaterialSubmission $materialSubmission, MaterialSubmissionUpdateDto $dto): void
    {
        $materialSubmission->assignedModerator()->associate(Auth::user());
        $this->update($materialSubmission, $dto, MaterialSubmissionStatus::Rejected);

        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(
                type: MaterialSubmissionActionType::Reject,
                details: $dto->actionDetails
            )
        );
    }

    public function update(
        MaterialSubmission          $materialSubmission,
        MaterialSubmissionUpdateDto $dto,
        ?MaterialSubmissionStatus   $status = null,
        ?Carbon                     $dateTime = null
    ): void
    {
        $dateTime ??= Carbon::now();

        if ($materialSubmission->type !== SubmissionType::Delete) {
            if ($dto->material !== null && $materialSubmission->type === SubmissionType::Create) {
                $this->materialService->update($materialSubmission->material, $dto->material);
            }

            if ($dto->materialState !== null) {
                $this->materialStateService->update($materialSubmission->materialState, $dto->materialState);
            }

            $category = $this->categoryRegistry->get($materialSubmission->material->category);

            if ($dto->versionSubmissions !== null && $category->isDownloadable()) {
                $versionSubmissionIds = collect($dto->versionSubmissions)
                    ->pluck('id')
                    ->filter()
                    ->all();

                $materialSubmission->versionSubmissions
                    ->reject(fn($versionSubmission) => in_array($versionSubmission->id, $versionSubmissionIds))
                    ->each(fn($oldSubmission) => $this->versionSubmissionService->delete($oldSubmission));

                foreach ($dto->versionSubmissions as $versionSubmissionDto) {
                    if ($versionSubmissionDto->id === null) {
                        $this->versionSubmissionService->create($materialSubmission, $versionSubmissionDto);
                    } else {
                        $versionSubmission = $materialSubmission->versionSubmissions->firstWhere('id', $versionSubmissionDto->id);
                        if ($versionSubmission) {
                            $this->versionSubmissionService->update($versionSubmission, $versionSubmissionDto);
                        }
                    }
                }
            } else {
                $materialSubmission->versionSubmissions->each(
                    fn($versionSubmission) => $this->versionSubmissionService->delete($versionSubmission)
                );
            }
        }

        if ($status !== null) {
            $materialSubmission->status = $status;
        }
        $materialSubmission->updated_at = $dateTime;
        $materialSubmission->save();
    }

    private function create(
        MaterialSubmissionCreateDto $dto,
        MaterialSubmissionStatus    $status
    ): MaterialSubmission
    {
        $material = $dto->type === SubmissionType::Create
            ? $this->materialService->create($dto->material)
            : Material::find($dto->material->id);

        $materialSubmission = MaterialSubmission::make();
        if ($dto->type !== SubmissionType::Delete) {
            $materialState = $this->materialStateService->create($material, $dto->materialState);
            $materialSubmission->materialState()->associate($materialState);
        }
        $materialSubmission->submitter()->associate(Auth::user());
        $materialSubmission->material()->associate($material);
        $materialSubmission->type   = $dto->type;
        $materialSubmission->status = $status;
        $materialSubmission->save();

        $category = $this->categoryRegistry->get($material->category);

        if (
            $dto->versionSubmissions !== null
            && $category->isDownloadable()
            && $materialSubmission->type !== SubmissionType::Delete
        ) {
            foreach ($dto->versionSubmissions as $versionSubmissionDto) {
                $this->versionSubmissionService->create($materialSubmission, $versionSubmissionDto);
            }
        }

        return $materialSubmission;
    }

    private function delete(MaterialSubmission $materialSubmission): void
    {
        $isUnreleasedMaterial = $materialSubmission->type === SubmissionType::Create
            && $materialSubmission->status !== MaterialSubmissionStatus::Accepted;
        $isRemovedMaterial    = $materialSubmission->type === SubmissionType::Delete
            && $materialSubmission->status === MaterialSubmissionStatus::Accepted;

        if ($isUnreleasedMaterial || $isRemovedMaterial) {
            $this->materialService->delete($materialSubmission->material);
            return;
        }

        if ($materialSubmission->type === SubmissionType::Delete) {
            $materialSubmission->delete();
            return;
        }

        $materialSubmission->versionSubmissions->each(
            fn($versionSubmission) => $this->versionSubmissionService->delete($versionSubmission)
        );
        $this->materialStateService->delete($materialSubmission->materialState);
    }
}
