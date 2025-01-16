<?php

namespace App\Rules;

use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\MaterialSubmission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaterialHasNoSubmissionRule implements ValidationRule
{
    /**
     * @param int $value Id материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            MaterialSubmission
                ::where('material_id', $value)
                ->whereIn('status', [MaterialSubmissionStatus::Draft, MaterialSubmissionStatus::Pending])
                ->exists()
        ) {
            $fail("Нельзя создавать одновременно более одной заявки на публикацию для одного и того же материала.");
        }
    }
}
