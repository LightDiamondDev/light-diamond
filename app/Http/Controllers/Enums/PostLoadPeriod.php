<?php

namespace App\Http\Controllers\Enums;

enum PostLoadPeriod: string
{
    case Day = 'DAY';
    case Week = 'WEEK';
    case Month = 'MONTH';
    case Year = 'YEAR';
    case AllTime = 'ALL_TIME';
}
