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
use App\Services\BotNotification\BotNotificationService;
use App\Services\Material\Dto\MaterialSubmissionActionDto;
use App\Services\Material\Dto\MaterialSubmissionCreateDto;
use App\Services\Material\Dto\MaterialSubmissionUpdateDto;
use App\Services\MaterialVersion\MaterialVersionService;
use App\Services\MaterialVersion\MaterialVersionSubmissionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

readonly class MaterialSubmissionService
{
    public function __construct(
        private BotNotificationService           $botNotificationService,
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
                    'message'      => $message,
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

        if (!Auth::user()->is_moderator) {
            $this->botNotificationService->notifyNewSubmission($materialSubmission);
        }

        return $materialSubmission;
    }

    public function submit(MaterialSubmission $materialSubmission, MaterialSubmissionUpdateDto $dto): void
    {
        $this->update($materialSubmission, $dto, MaterialSubmissionStatus::Pending);

        $this->actionService->create(
            $materialSubmission,
            new MaterialSubmissionActionDto(MaterialSubmissionActionType::Submit)
        );

        if (!Auth::user()->is_moderator) {
            $this->botNotificationService->notifyNewSubmission($materialSubmission);
        }
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

    public function create(
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

                $removedVersionSubmissionKeys = $materialSubmission->versionSubmissions
                    ->reject(fn($versionSubmission) => in_array($versionSubmission->id, $versionSubmissionIds))
                    ->each(fn($oldVersionSubmission) => $this->versionSubmissionService->delete($oldVersionSubmission))
                    ->keys();
                $materialSubmission->versionSubmissions->forget($removedVersionSubmissionKeys);

                foreach ($dto->versionSubmissions as $versionSubmissionDto) {
                    if ($versionSubmissionDto->id === null) {
                        $newVersionSubmission = $this->versionSubmissionService->create($materialSubmission, $versionSubmissionDto);
                        $materialSubmission->versionSubmissions->push($newVersionSubmission);
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

    public function delete(MaterialSubmission $materialSubmission): void
    {
        if ($materialSubmission->type !== SubmissionType::Delete) {
            $materialSubmission->versionSubmissions->each(
                fn($versionSubmission) => $this->versionSubmissionService->delete($versionSubmission)
            );
        }

        if ($this->shouldDeleteMaterial($materialSubmission)) {
            $this->materialService->delete($materialSubmission->material);
            return;
        }

        if ($this->canDeleteState($materialSubmission)) {
            $this->materialStateService->delete($materialSubmission->materialState);
        } else {
            $materialSubmission->delete();
        }

        $this->deletePreviousStateIfNeeded($materialSubmission);
    }

    private function shouldDeleteMaterial(MaterialSubmission $materialSubmission): bool
    {
        $isUnreleased = $materialSubmission->type === SubmissionType::Create
            && $materialSubmission->status !== MaterialSubmissionStatus::Accepted;
        $isRemoved    = $materialSubmission->type === SubmissionType::Delete
            && $materialSubmission->status === MaterialSubmissionStatus::Accepted;

        return $isUnreleased || $isRemoved;
    }

    private function canDeleteState(MaterialSubmission $materialSubmission): bool
    {
        if ($materialSubmission->type === SubmissionType::Delete) {
            return false;
        }

        if ($materialSubmission->status === MaterialSubmissionStatus::Accepted) {
            $latestMaterialStateId = MaterialState::where('material_id', $materialSubmission->material_id)
                ->orderByDesc('published_at')
                ->value('id');

            if ($latestMaterialStateId === $materialSubmission->material_state_id) {
                return false;
            }

            $isStatePreviousForAnySubmission = MaterialSubmission::closed()
                ->where('material_id', $materialSubmission->material_id)
                ->where('updated_at', '>', $materialSubmission->materialState->published_at)
                ->where(function (Builder $query) use ($materialSubmission) {
                    // id последнего опубликованного состояния на момент до закрытия заявки
                    $query->select('id')
                        ->from('material_states')
                        ->whereNotNull('published_at')
                        ->whereColumn('material_states.material_id', 'material_submissions.material_id')
                        ->whereColumn('material_states.published_at', '<', 'material_submissions.updated_at')
                        ->orderByDesc('material_states.published_at')
                        ->limit(1);
                }, $materialSubmission->material_state_id)
                ->exists();

            if ($isStatePreviousForAnySubmission) {
                return false;
            }
        }

        return true;
    }

    private function deletePreviousStateIfNeeded(MaterialSubmission $materialSubmission): void
    {
        if ($materialSubmission->is_closed) {
            $previousState = MaterialState::where('material_id', $materialSubmission->material_id)
                ->where('published_at', '<', $materialSubmission->updated_at)
                ->orderByDesc('published_at')
                ->withExists('materialSubmission')
                ->first();

            if ($previousState !== null && !$previousState->material_submission_exists) {
                $this->materialStateService->delete($previousState);
            }
        }
    }
}
