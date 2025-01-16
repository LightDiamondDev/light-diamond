<?php

namespace App\Rules;

use App\Models\Enums\SubmissionType;
use App\Models\Material;
use Auth;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class MaterialVersionSubmissionListRule implements ValidationRule
{
    const int MAX_NEW_VERSIONS = 1;

    public function __construct(private ?Material $material)
    {
    }

    /**
     * @param array $value Список подач версий
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

        if ($this->material === null) {
            $totalVersionCount = $createCount;
        } else {
            $publishedVersionCount = $this->material->versions()->count();
            $totalVersionCount     = $publishedVersionCount - $deleteCount + $createCount;
        }

        if ($totalVersionCount < 1) {
            $fail('Материал должен содержать как минимум одну версию.');
            return;
        }

        if ($createCount > self::MAX_NEW_VERSIONS && !Auth::user()->is_moderator) {
            $fail('Максимальное количество создаваемых версий в одной заявке - ' . self::MAX_NEW_VERSIONS . '.');
        }
    }
}
