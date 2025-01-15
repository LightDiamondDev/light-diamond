<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Schema;

readonly class ColumnExistsRule implements ValidationRule
{
    public function __construct(private string $table)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Schema::hasColumn($this->table, $value)) {
            $fail('Колонка с таким названием не существует.');
        }
    }
}
