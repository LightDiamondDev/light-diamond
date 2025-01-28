<?php

namespace App\Services\MaterialVersion;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use App\Models\MaterialSubmission;
use App\Models\MaterialVersion;
use App\Models\MaterialVersionSubmission;
use App\Services\MaterialFile\MaterialFileSubmissionService;
use App\Services\MaterialVersion\Dto\MaterialVersionSubmissionDto;

readonly class MaterialVersionSubmissionService
{
    public function __construct(
        private MaterialVersionService        $versionService,
        private MaterialVersionStateService   $versionStateService,
        private MaterialFileSubmissionService $fileSubmissionService,
    )
    {
    }

    public function create(MaterialSubmission $materialSubmission, MaterialVersionSubmissionDto $dto): MaterialVersionSubmission
    {
        $version = $dto->type === SubmissionType::Create
            ? $this->versionService->create($materialSubmission->material)
            : MaterialVersion::find($dto->versionId);

        $versionSubmission = MaterialVersionSubmission::make();
        if ($dto->type !== SubmissionType::Delete) {
            $state = $this->versionStateService->create($version, $dto->versionState);
            $versionSubmission->versionState()->associate($state);
        }
        $versionSubmission->version()->associate($version);
        $versionSubmission->materialSubmission()->associate($materialSubmission);
        $versionSubmission->type = $dto->type;
        $versionSubmission->save();

        if ($dto->fileSubmissions !== null && $versionSubmission->type !== SubmissionType::Delete) {
            foreach ($dto->fileSubmissions as $fileSubmissionDto) {
                $this->fileSubmissionService->create($versionSubmission, $fileSubmissionDto);
            }
        }

        return $versionSubmission;
    }

    public function update(MaterialVersionSubmission $versionSubmission, MaterialVersionSubmissionDto $dto): void
    {
        if ($versionSubmission->type === SubmissionType::Delete) {
            return;
        }

        if ($dto->versionState !== null) {
            $this->versionStateService->update($versionSubmission->versionState, $dto->versionState);
        }

        if ($dto->fileSubmissions !== null) {
            $fileSubmissionIds = collect($dto->fileSubmissions)
                ->pluck('id')
                ->filter()
                ->all();

            $removedFileSubmissionKeys = $versionSubmission->fileSubmissions
                ->reject(fn($fileSubmission) => in_array($fileSubmission->id, $fileSubmissionIds))
                ->each(fn($oldFileSubmission) => $this->fileSubmissionService->delete($oldFileSubmission))
                ->keys();
            $versionSubmission->fileSubmissions->forget($removedFileSubmissionKeys);

            foreach ($dto->fileSubmissions as $fileSubmissionDto) {
                if ($fileSubmissionDto->id === null) {
                    $newFileSubmission = $this->fileSubmissionService->create($versionSubmission, $fileSubmissionDto);
                    $versionSubmission->fileSubmissions->push($newFileSubmission);
                } else {
                    $fileSubmission = $versionSubmission->fileSubmissions->firstWhere('id', $fileSubmissionDto->id);
                    if ($fileSubmission) {
                        $this->fileSubmissionService->update($fileSubmission, $fileSubmissionDto);
                    }
                }
            }
        }
    }

    public function delete(MaterialVersionSubmission $versionSubmission): void
    {
        $isUnreleasedVersion = $versionSubmission->type === SubmissionType::Create
            && $versionSubmission->materialSubmission->status !== MaterialSubmissionStatus::Accepted;
        $isRemovedVersion    = $versionSubmission->type === SubmissionType::Delete
            && $versionSubmission->materialSubmission->status === MaterialSubmissionStatus::Accepted;

        if ($isUnreleasedVersion || $isRemovedVersion) {
            $this->versionService->delete($versionSubmission->version);
            return;
        }

        if ($versionSubmission->type === SubmissionType::Delete) {
            $versionSubmission->delete();
            return;
        }

        $versionSubmission->fileSubmissions->each(
            fn($fileSubmission) => $this->fileSubmissionService->delete($fileSubmission)
        );
        $this->versionStateService->delete($versionSubmission->versionState);
    }
}
