<?php

namespace App\Http\Controllers\Enums;

enum MaterialSortType: string
{
    case Latest = 'LATEST';
    case Popular = 'POPULAR';
}
