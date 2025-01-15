<?php

namespace App\Services\MaterialVersion\Dto;

use App\Services\DtoTrait;

class MaterialVersionLocalizationDto
{
    use DtoTrait;

    public function __construct(
        public ?int    $id = null,
        public ?string $language = null,
        public ?string $name = null,
        public ?string $changelog = null
    )
    {
    }
}
