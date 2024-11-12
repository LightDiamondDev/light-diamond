<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;

class ImageFileExistsRule implements ValidationRule
{
    /**
     * @param string $value Путь к файлу изображения в хранилище
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Storage::disk('public')->exists($value)) {
            $fail("Не существует файла изображения по указанному пути.");
        }
    }
}
