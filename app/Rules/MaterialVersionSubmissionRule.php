<?php

namespace App\Rules;

use App\Models\MaterialSubmission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class MaterialVersionSubmissionRule implements ValidationRule
{
    public function __construct(private MaterialSubmission $materialSubmission)
    {
    }

    /**
     * @param int $value Id подачи версии материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->materialSubmission->versionSubmissions()->where('id', $value)->exists()) {
            $fail('Не существует подачи версии с id ' . $value . ' у заявки на публикацию с id ' . $this->materialSubmission->id . '.');
        }
    }
}
