<?php

namespace App\Services\Dto;

use App\Models\Enums\PostVersionActionType;
use App\Models\PostVersion;
use App\Models\User;

readonly class PostVersionActionDto
{
    public function __construct(
        public PostVersion           $version,
        public User                  $user,
        public PostVersionActionType $type,
        public array                 $details = [],
    )
    {
    }
}
