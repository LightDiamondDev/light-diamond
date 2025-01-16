<?php

namespace App\Rules;

use App\Models\MaterialSubmission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class MaterialLocalizationRule implements ValidationRule
{
    public function __construct(private MaterialSubmission $materialSubmission)
    {
    }

    /**
     * @param int $value Id локализации версии материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $localizationExists = $this->materialSubmission
            ->join('material_states', 'material_states.id', '=', 'material_submissions.material_state_id')
            ->join('material_localizations', 'material_localizations.material_state_id', '=', 'material_states.id')
            ->where('material_localizations.id', $value)
            ->exists();

        if (!$localizationExists) {
            $fail('Не существует локализации материала с id ' . $value . ' у заявки на публикацию с id ' . $materialSubmissionId . '.');
        }
    }
}
