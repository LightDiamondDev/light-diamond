<?php

namespace App\Services\Dto;

class PostVersionFileDto
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?string $name,
        public readonly ?string $path,
        public readonly ?string $url,
        public readonly ?int    $size,
    )
    {
    }
}
