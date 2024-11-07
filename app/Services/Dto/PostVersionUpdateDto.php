<?php

namespace App\Services\Dto;

use App\Registries\CategoryType;
use Illuminate\Http\UploadedFile;

readonly class PostVersionUpdateDto
{
    /**
     * @param PostVersionFileDto[] $files
     */
    public function __construct(
        public ?CategoryType $category = null,
        public ?string       $title = null,
        public ?UploadedFile $coverFile = null,
        public ?string       $description = null,
        public ?string       $content = null,
        public ?array        $actionDetails = null,
        public ?string       $slug = null,
        public ?array        $files = null,
    )
    {
    }
}
