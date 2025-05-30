<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class HtmlMinSymbolCountRule implements ValidationRule
{
    public function __construct(private int $minCount)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (mb_strlen(strip_tags($value)) < $this->minCount) {
            $fail("Количество символов должно быть не меньше $this->minCount.");
        }
    }
}
