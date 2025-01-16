<?php

namespace App\Rules;

use App\Models\Material;
use App\Models\MaterialVersion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class MaterialVersionRule implements ValidationRule
{
    public function __construct(private ?Material $material)
    {
    }

    /**
     * @param int $value id версии
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $version = MaterialVersion::withoutGlobalScopes()->find($value);
        if ($version === null) {
            $fail("Версия материала с id $value не найдена.");
            return;
        }

        if ($this->material !== null) {
            $specifiedMaterialId = $this->material->id;
            if ($version->material_id !== $specifiedMaterialId) {
                $fail("Материал с id $specifiedMaterialId не имеет версии с id $value.");
            }
        }
    }
}
