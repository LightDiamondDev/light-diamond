<?php

namespace App\Rules;

use App\Models\Material;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class OwnMaterialRule implements ValidationRule
{
    /**
     * @param int $value Id материала
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Material|null $material */
        $material = Material::withoutGlobalScopes()->find($value);

        if ($material === null || $material->state->author_id !== Auth::id()) {
            $fail("Материал с id $value не существует или принадлежит не вам.");
        }
    }
}
