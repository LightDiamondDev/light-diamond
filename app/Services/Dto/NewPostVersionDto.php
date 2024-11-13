<?php

namespace App\Services\Dto;

use App\Models\User;
use App\Registries\CategoryType;

readonly class NewPostVersionDto
{
    /**
     * @param PostVersionFileDto[] $files
     */
    public function __construct(
        public ?int         $postId,
        public User         $author,
        public CategoryType $category,
        public string       $title,
        public string       $cover,
        public string       $description,
        public string       $content,
        public array        $files,
    )
    {
    }
}
