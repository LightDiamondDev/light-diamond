<?php

namespace App\Services\Dto;

use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class NewPostVersionDto
{
    /**
     * @param PostVersionFileDto[] $files
     */
    public function __construct(
        public readonly User         $author,
        public readonly PostCategory $category,
        public readonly string       $title,
        public readonly UploadedFile $coverFile,
        public readonly string       $description,
        public readonly string       $content,
        public readonly array        $files,
    )
    {
    }
}
