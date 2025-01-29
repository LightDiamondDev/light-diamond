<?php

namespace App\Rules;

use App\Models\Enums\GameEdition;
use App\Models\Material;
use App\Registries\CategoryType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class MaterialSlugRule implements ValidationRule
{
    public function __construct(private ?GameEdition $gameEdition, private CategoryType $category)
    {
    }

    /**
     * @param string $value URL-идентификатор материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $value)) {
            $fail('Разрешены только английские буквы, цифры и дефисы.');
            return;
        }

        if (preg_match('/^-+$/i', $value)) {
            $fail('Нельзя использовать только дефисы.');
            return;
        }

        $exists = Material::whereSlug(strtolower($value))
            ->whereEdition($this->gameEdition?->value)
            ->whereCategory($this->category->value)
            ->exists();
        if ($exists) {
            $fail('Данный URL-идентификатор уже занят.');
        }
    }
}
