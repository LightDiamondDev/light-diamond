<?php

namespace App\Models\Enums;

enum MaterialSubmissionActionType: string
{
    case Submit          = 'SUBMIT';
    case RequestChanges  = 'REQUEST_CHANGES';
    case Accept          = 'ACCEPT';
    case Reject          = 'REJECT';
    case AssignModerator = 'ASSIGN_MODERATOR';
    case Reconsider      = 'RECONSIDER';
    case Message         = 'MESSAGE';
}
