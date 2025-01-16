<?php

namespace App\Rules;

use App\Models\Enums\SubmissionType;
use App\Models\MaterialFileSubmission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaterialFileSubmissionTypeRule implements ValidationRule
{
    /**
     * @param SubmissionType $value Тип подачи файла материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        preg_match('/^version_submissions\.\d+\.file_submissions\.\d+/', $attribute, $matches);
        $fileSubmissionId = data_get(request(), $matches[0] . '.id');

        if ($fileSubmissionId !== null) {
            $fileSubmission = MaterialFileSubmission::find($fileSubmissionId);
            if ($fileSubmission !== null && $fileSubmission->type->value !== $value) {
                $fail('Указанный тип подачи файла не совпадает с существующим.');
            }
        }
    }
}
