<?php

namespace App\Services\Material\Dto;

use App\Models\Enums\GameEdition;
use App\Registries\CategoryType;
use App\Services\DtoTrait;

class MaterialDto
{
    use DtoTrait;

    public function __construct(
        public ?int          $id = null,
        public ?string       $slug = null,
        public ?CategoryType $category = null,
        public ?GameEdition  $edition = null,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return self::fromArrayInternal($data, function ($key, $value) {
            return match ($key) {
                'category' => CategoryType::from($value),
                'edition'  => GameEdition::from($value),
                default    => $value,
            };
        });
    }
}
