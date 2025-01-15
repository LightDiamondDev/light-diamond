<?php

namespace App\Rules;

use App\Registries\Types\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class MaterialEditionRule implements ValidationRule
{
    public function __construct(private ?Category $category)
    {
    }

    /**
     * @param string $value Издание материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->category === null) {
            return;
        }

        $categoryEdition = $this->category->getEdition();

        if ($categoryEdition !== null && $categoryEdition->value !== $value) {
            $fail('Данное издание не совместимо с выбранной категорией.');
        }
    }
}
