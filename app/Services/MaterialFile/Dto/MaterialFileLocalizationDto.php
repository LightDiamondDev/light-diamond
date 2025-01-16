<?php

namespace App\Services\MaterialFile\Dto;

use App\Services\DtoTrait;

class MaterialFileLocalizationDto
{
    use DtoTrait;

    public function __construct(
        public ?int    $id = null,
        public ?string $language = null,
        public ?string $name = null
    )
    {
    }
}
