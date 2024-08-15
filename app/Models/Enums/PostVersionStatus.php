<?php

namespace App\Models\Enums;

enum PostVersionStatus: string
{
    case Draft = 'DRAFT';
    case Pending = 'PENDING';
    case Accepted = 'ACCEPTED';
    case Rejected = 'REJECTED';
}
