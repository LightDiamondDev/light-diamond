<?php

namespace App\Rules;

use App\Models\MaterialFileSubmission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaterialFileSubmissionRule implements ValidationRule
{
    /**
     * @param int $value Id подачи файла материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        preg_match('/^version_submissions\.\d+/', $attribute, $matches);
        $versionSubmissionId = data_get(request(), $matches[0] . '.id');

        if (!MaterialFileSubmission::where('version_submission_id', $versionSubmissionId)->where('id', $value)->exists()) {
            $fail('Не существует подачи файла с id ' . $value . ' у подачи версии с id ' . $versionSubmissionId . '.');
        }
    }
}
