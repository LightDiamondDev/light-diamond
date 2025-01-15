<?php

namespace App\Rules;

use App\Models\Enums\SubmissionType;
use App\Models\MaterialFile;
use Auth;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaterialFileSubmissionListRule implements ValidationRule
{
    const int MAX_FILES_PER_VERSION = 3;

    /**
     * @param array $value Список подач файлов
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $createCount = 0;
        $deleteCount = 0;

        foreach ($value as $submissionData) {
            switch ($submissionData['type']) {
                case SubmissionType::Create->value:
                    $createCount++;
                    break;
                case SubmissionType::Delete->value:
                    $deleteCount++;
                    break;
            }
        }

        preg_match('/^version_submissions\.\d+/', $attribute, $matches);
        $versionId = data_get(request(), $matches[0] . '.version_id');
        if ($versionId === null) {
            $totalFileCount = $createCount;
        } else {
            $publishedFileCount = MaterialFile::where('version_id', $versionId)->count();
            $totalFileCount     = $publishedFileCount - $deleteCount + $createCount;
        }

        if ($totalFileCount < 1) {
            $fail('Версия должна содержать как минимум один файл.');
            return;
        }
        if ($totalFileCount > self::MAX_FILES_PER_VERSION && !Auth::user()->is_moderator) {
            $fail('Максимальное количество файлов на версию - ' . self::MAX_FILES_PER_VERSION . '.');
        }
    }
}
