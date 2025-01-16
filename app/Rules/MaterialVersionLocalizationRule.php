<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class MaterialVersionLocalizationRule implements ValidationRule
{
    /**
     * @param int $value Id локализации версии материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        preg_match('/^version_submissions\.\d+/', $attribute, $matches);
        $versionSubmissionId = data_get(request(), $matches[0] . '.id');

        $localizationExists = DB::table('material_version_submissions')
            ->join('material_version_states', 'material_version_states.id', '=', 'material_version_submissions.version_state_id')
            ->join('material_version_localizations', 'material_version_localizations.version_state_id', '=', 'material_version_states.id')
            ->where('material_version_submissions.id', $versionSubmissionId)
            ->where('material_version_localizations.id', $value)
            ->exists();

        if (!$localizationExists) {
            $fail('Не существует локализации версии с id ' . $value . ' у подачи версии с id ' . $versionSubmissionId . '.');
        }
    }
}
