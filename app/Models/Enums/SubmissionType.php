<?php

namespace App\Models\Enums;

enum SubmissionType: string
{
    case Create = 'CREATE';
    case Update = 'UPDATE';
    case Delete = 'DELETE';
}
