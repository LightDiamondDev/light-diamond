<?php

namespace App\Models\Enums;

enum PostVersionActionType: string
{
    case Submit = 'SUBMIT';
    case RequestChanges = 'REQUEST_CHANGES';
    case Accept = 'ACCEPT';
    case Reject = 'REJECT';
    case AssignModerator = 'ASSIGN_MODERATOR';
}
