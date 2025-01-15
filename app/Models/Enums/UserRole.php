<?php

namespace App\Models\Enums;

enum UserRole: string
{
    case User      = 'USER';
    case Moderator = 'MODERATOR';
    case Admin     = 'ADMIN';
}
