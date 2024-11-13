<?php

namespace App\Services\Dto;

use App\Registries\CategoryType;

readonly class PostVersionUpdateDto
{
    /**
     * @param PostVersionFileDto[] $files
     */
    public function __construct(
        public ?CategoryType $category = null,
        public ?string       $title = null,
        public ?string       $cover = null,
        public ?string       $description = null,
        public ?string       $content = null,
        public ?array        $actionDetails = null,
        public ?string       $slug = null,
        public ?array        $files = null,
    )
    {
    }
}
