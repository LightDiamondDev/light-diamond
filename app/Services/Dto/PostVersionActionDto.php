<?php

namespace App\Services\Dto;

use App\Models\Enums\PostVersionActionType;
use App\Models\PostVersion;
use App\Models\User;

class PostVersionActionDto
{
    public function __construct(
        public readonly PostVersion           $version,
        public readonly User                  $user,
        public readonly PostVersionActionType $type,
        public readonly array                 $details = [],
    )
    {
    }
}
