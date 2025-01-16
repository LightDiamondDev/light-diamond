<?php

namespace App\Services\MaterialFile\Dto;

use App\Services\DtoTrait;

class MaterialFileDto
{
    use DtoTrait;

    public function __construct(
        public ?int    $id = null,
        public ?string $path = null,
        public ?string $url = null,
        public ?int    $size = null,
        public ?string $extension = null
    )
    {
    }
}
