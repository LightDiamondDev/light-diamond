<?php

namespace App\Services\Material\Dto;

use App\Services\DtoTrait;

class MaterialLocalizationDto
{
    use DtoTrait;

    public function __construct(
        public ?int    $id = null,
        public ?string $language = null,
        public ?string $cover = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $content = null
    )
    {
    }
}
