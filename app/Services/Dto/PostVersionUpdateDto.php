<?php

namespace App\Services\Dto;

use Illuminate\Http\UploadedFile;

class PostVersionUpdateDto
{
    /**
     * @param PostVersionFileDto[] $files
     */
    public function __construct(
        public readonly ?int          $categoryId = null,
        public readonly ?string       $title = null,
        public readonly ?UploadedFile $coverFile = null,
        public readonly ?string       $description = null,
        public readonly ?string       $content = null,
        public readonly ?array        $actionDetails = null,
        public readonly ?string       $slug = null,
        public readonly ?array        $files = null,
    )
    {
    }
}
