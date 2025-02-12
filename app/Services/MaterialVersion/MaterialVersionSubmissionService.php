<?php

namespace App\Services\MaterialVersion;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use App\Models\MaterialSubmission;
use App\Models\MaterialVersion;
use App\Models\MaterialVersionState;
use App\Models\MaterialVersionSubmission;
use App\Services\MaterialFile\MaterialFileSubmissionService;
use App\Services\MaterialVersion\Dto\MaterialVersionSubmissionDto;
use Illuminate\Database\Query\Builder;

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
        if ($versionSubmission->type !== SubmissionType::Delete) {
            $versionSubmission->fileSubmissions->each(
                fn($fileSubmission) => $this->fileSubmissionService->delete($fileSubmission)
            );
        }

        if ($this->shouldDeleteVersion($versionSubmission)) {
            $this->versionService->delete($versionSubmission->version);
            return;
        }

        if ($this->canDeleteState($versionSubmission)) {
            $this->versionStateService->delete($versionSubmission->versionState);
        } else {
            $versionSubmission->delete();
        }

        $this->deletePreviousStateIfNeeded($versionSubmission);
    }

    private function shouldDeleteVersion(MaterialVersionSubmission $versionSubmission): bool
    {
        $isUnreleased = $versionSubmission->type === SubmissionType::Create
            && $versionSubmission->status !== MaterialSubmissionStatus::Accepted;
        $isRemoved    = $versionSubmission->type === SubmissionType::Delete
            && $versionSubmission->status === MaterialSubmissionStatus::Accepted;

        return $isUnreleased || $isRemoved;
    }

    private function canDeleteState(MaterialVersionSubmission $versionSubmission): bool
    {
        if ($versionSubmission->type === SubmissionType::Delete) {
            return false;
        }

        if ($versionSubmission->status === MaterialSubmissionStatus::Accepted) {
            $latestVersionStateId = MaterialVersionState::where('version_id', $versionSubmission->version_id)
                ->orderByDesc('published_at')
                ->value('id');

            if ($latestVersionStateId === $versionSubmission->version_state_id) {
                return false;
            }

            $isStatePreviousForAnySubmission = MaterialVersionSubmission
                ::join('material_submissions', 'material_submissions.id', '=', 'material_version_submissions.material_submission_id')
                ->where('version_id', $versionSubmission->version_id)
                ->whereIn('material_submissions.status', [MaterialSubmissionStatus::Accepted, MaterialSubmissionStatus::Rejected])
                ->where('material_submissions.updated_at', '>', $versionSubmission->versionState->published_at)
                ->where(function (Builder $query) use ($versionSubmission) {
                    // id последнего опубликованного состояния на момент до закрытия заявки
                    $query->select('id')
                        ->from('material_version_states')
                        ->whereNotNull('published_at')
                        ->whereColumn('material_version_states.version_id', 'material_version_submissions.version_id')
                        ->whereColumn('material_version_states.published_at', '<', 'material_submissions.updated_at')
                        ->orderByDesc('material_version_states.published_at')
                        ->limit(1);
                }, $versionSubmission->version_state_id)
                ->exists();

            if ($isStatePreviousForAnySubmission) {
                return false;
            }
        }

        return true;
    }

    private function deletePreviousStateIfNeeded(MaterialVersionSubmission $versionSubmission): void
    {
        if ($versionSubmission->is_closed) {
            $previousState = MaterialVersionState::where('version_id', $versionSubmission->version_id)
                ->where('published_at', '<', $versionSubmission->updated_at)
                ->orderByDesc('published_at')
                ->withExists('versionSubmission')
                ->first();

            if ($previousState !== null && !$previousState->version_submission_exists) {
                $this->versionStateService->delete($previousState);
            }
        }
    }
}
