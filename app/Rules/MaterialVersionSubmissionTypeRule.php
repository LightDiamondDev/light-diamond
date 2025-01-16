<?php

namespace App\Rules;

use App\Models\Enums\SubmissionType;
use App\Models\MaterialVersionSubmission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaterialVersionSubmissionTypeRule implements ValidationRule
{
    /**
     * @param SubmissionType $value Тип подачи версии материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        preg_match('/^version_submissions\.\d+/', $attribute, $matches);
        $versionSubmissionId = data_get(request(), $matches[0] . '.id');

        if ($versionSubmissionId !== null) {
            $versionSubmission = MaterialVersionSubmission::find($versionSubmissionId);
            if ($versionSubmission !== null && $versionSubmission->type->value !== $value) {
                $fail('Указанный тип подачи версии не совпадает с существующим.');
            }
        }
    }
}
