<?php

namespace App\Models\Enums;

enum MaterialSubmissionStatus: string
{
    case Draft    = 'DRAFT';
    case Pending  = 'PENDING';
    case Accepted = 'ACCEPTED';
    case Rejected = 'REJECTED';
}
