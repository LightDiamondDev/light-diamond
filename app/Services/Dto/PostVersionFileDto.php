<?php

namespace App\Services\Dto;

readonly class PostVersionFileDto
{
    public function __construct(
        public ?int    $id,
        public ?string $name,
        public ?string $path,
        public ?string $url,
        public ?int    $size,
        public ?string $extension,
    )
    {
    }
}
