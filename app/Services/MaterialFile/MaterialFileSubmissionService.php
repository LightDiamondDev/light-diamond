<?php

namespace App\Services\MaterialFile;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use App\Models\MaterialFile;
use App\Models\MaterialFileState;
use App\Models\MaterialFileSubmission;
use App\Models\MaterialVersionSubmission;
use App\Services\MaterialFile\Dto\MaterialFileSubmissionDto;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

readonly class MaterialFileSubmissionService
{
    public function __construct(
        private MaterialFileService      $fileService,
        private MaterialFileStateService $fileStateService,
    )
    {
    }

    public function create(MaterialVersionSubmission $versionSubmission, MaterialFileSubmissionDto $dto): MaterialFileSubmission
    {
        $file = $dto->type === SubmissionType::Create
            ? $this->fileService->create($versionSubmission->version, $dto->file)
            : MaterialFile::find($dto->file->id);

        $fileSubmission = MaterialFileSubmission::make();
        if ($dto->type !== SubmissionType::Delete) {
            $state = $this->fileStateService->create($file, $dto->fileState);
            $fileSubmission->fileState()->associate($state);
        }
        $fileSubmission->file()->associate($file);
        $fileSubmission->versionSubmission()->associate($versionSubmission);
        $fileSubmission->type = $dto->type;
        $fileSubmission->save();

        return $fileSubmission;
    }

    public function update(MaterialFileSubmission $fileSubmission, MaterialFileSubmissionDto $dto): void
    {
        if ($fileSubmission->type === SubmissionType::Delete) {
            return;
        }

        if (
            $dto->file !== null &&
            ($fileSubmission->type === SubmissionType::Create || Auth::user()->is_moderator)
        ) {
            $this->fileService->update($fileSubmission->file, $dto->file);
        }

        if ($dto->fileState !== null) {
            $this->fileStateService->update($fileSubmission->fileState, $dto->fileState);
        }
    }

    public function delete(MaterialFileSubmission $fileSubmission): void
    {
        if ($this->shouldDeleteFile($fileSubmission)) {
            $this->fileService->delete($fileSubmission->file);
            return;
        }

        if ($this->canDeleteState($fileSubmission)) {
            $this->fileStateService->delete($fileSubmission->fileState);
        } else {
            $fileSubmission->delete();
        }

        $this->deletePreviousStateIfNeeded($fileSubmission);
    }

    private function shouldDeleteFile(MaterialFileSubmission $fileSubmission): bool
    {
        $isUnreleased = $fileSubmission->type === SubmissionType::Create
            && $fileSubmission->status !== MaterialSubmissionStatus::Accepted;
        $isRemoved    = $fileSubmission->type === SubmissionType::Delete
            && $fileSubmission->status === MaterialSubmissionStatus::Accepted;

        return $isUnreleased || $isRemoved;
    }

    private function canDeleteState(MaterialFileSubmission $fileSubmission): bool
    {
        if ($fileSubmission->type === SubmissionType::Delete) {
            return false;
        }

        if ($fileSubmission->status === MaterialSubmissionStatus::Accepted) {
            $latestFileStateId = MaterialFileState::where('file_id', $fileSubmission->file_id)
                ->orderByDesc('published_at')
                ->value('id');

            if ($latestFileStateId === $fileSubmission->file_state_id) {
                return false;
            }

            $isStatePreviousForAnySubmission = MaterialFileSubmission
                ::join('material_version_submissions', 'material_version_submissions.id', '=', 'material_file_submissions.version_submission_id')
                ->join('material_submissions', 'material_submissions.id', '=', 'material_version_submissions.material_submission_id')
                ->where('file_id', $fileSubmission->file_id)
                ->whereIn('material_submissions.status', [MaterialSubmissionStatus::Accepted, MaterialSubmissionStatus::Rejected])
                ->where('material_submissions.updated_at', '>', $fileSubmission->fileState->published_at)
                ->where(function (Builder $query) use ($fileSubmission) {
                    // id последнего опубликованного состояния на момент до закрытия заявки
                    $query->select('id')
                        ->from('material_file_states')
                        ->whereNotNull('published_at')
                        ->whereColumn('material_file_states.file_id', 'material_file_submissions.file_id')
                        ->whereColumn('material_file_states.published_at', '<', 'material_submissions.updated_at')
                        ->orderByDesc('material_file_states.published_at')
                        ->limit(1);
                }, $fileSubmission->file_state_id)
                ->exists();

            if ($isStatePreviousForAnySubmission) {
                return false;
            }
        }

        return true;
    }

    private function deletePreviousStateIfNeeded(MaterialFileSubmission $fileSubmission): void
    {
        if ($fileSubmission->is_closed) {
            $previousState = MaterialFileState::where('file_id', $fileSubmission->file_id)
                ->where('published_at', '<', $fileSubmission->updated_at)
                ->orderByDesc('published_at')
                ->withExists('fileSubmission')
                ->first();

            if ($previousState !== null && !$previousState->file_submission_exists) {
                $this->fileStateService->delete($previousState);
            }
        }
    }
}
