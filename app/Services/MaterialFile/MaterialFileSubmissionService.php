<?php

namespace App\Services\MaterialFile;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use App\Models\MaterialFile;
use App\Models\MaterialFileSubmission;
use App\Models\MaterialVersionSubmission;
use App\Services\MaterialFile\Dto\MaterialFileSubmissionDto;
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
        $isUnreleasedFile = $fileSubmission->type === SubmissionType::Create
            && $fileSubmission->versionSubmission->materialSubmission->status !== MaterialSubmissionStatus::Accepted;
        $isRemovedFile    = $fileSubmission->type === SubmissionType::Delete
            && $fileSubmission->versionSubmission->materialSubmission->status === MaterialSubmissionStatus::Accepted;

        if ($isUnreleasedFile || $isRemovedFile) {
            $this->fileService->delete($fileSubmission->file);
            return;
        }

        if ($fileSubmission->type === SubmissionType::Delete) {
            $fileSubmission->delete();
            return;
        }

        $this->fileStateService->delete($fileSubmission->fileState);
    }
}
