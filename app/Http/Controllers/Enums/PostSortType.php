<?php

namespace App\Http\Controllers\Enums;

enum PostSortType: string
{
    case Latest = 'LATEST';
    case Popular = 'POPULAR';
}
