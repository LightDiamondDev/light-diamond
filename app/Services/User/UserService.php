<?php

namespace App\Services\User;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\MaterialSubmission;
use App\Models\User;

readonly class UserService
{
    const int MAX_ACTIVE_SUBMISSION_COUNT = 5;

    public function getMaximumActiveSubmissionCount(): int
    {
        return self::MAX_ACTIVE_SUBMISSION_COUNT;
    }

    public function getUserActiveSubmissionCount(User $user): int
    {
        return MaterialSubmission
            ::where('submitter_id', $user->id)
            ->whereIn('status', [MaterialSubmissionStatus::Draft, MaterialSubmissionStatus::Pending])
            ->count();
    }
}
