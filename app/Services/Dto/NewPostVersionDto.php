<?php

namespace App\Services\Dto;

use App\Models\User;
use App\Registries\CategoryType;
use Illuminate\Http\UploadedFile;

readonly class NewPostVersionDto
{
    /**
     * @param PostVersionFileDto[] $files
     */
    public function __construct(
        public int           $postId,
        public User          $author,
        public CategoryType  $category,
        public string        $title,
        public ?UploadedFile $coverFile,
        public ?string       $cover,
        public string        $description,
        public string        $content,
        public array         $files,
    )
    {
    }
}
