<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class MaterialFileLocalizationRule implements ValidationRule
{
    /**
     * @param int $value Id локализации файла материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        preg_match('/^version_submissions\.\d+\.file_submissions\.\d+/', $attribute, $matches);
        $fileSubmissionId = data_get(request(), $matches[0] . '.id');

        $localizationExists = DB::table('material_file_submissions')
            ->join('material_file_states', 'material_file_states.id', '=', 'material_file_submissions.file_state_id')
            ->join('material_file_localizations', 'material_file_localizations.file_state_id', '=', 'material_file_states.id')
            ->where('material_file_submissions.id', $fileSubmissionId)
            ->where('material_file_localizations.id', $value)
            ->exists();

        if (!$localizationExists) {
            $fail('Не существует локализации файла с id ' . $value . ' у подачи файла с id ' . $fileSubmissionId . '.');
        }
    }
}
