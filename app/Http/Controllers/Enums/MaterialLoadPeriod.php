<?php

namespace App\Http\Controllers\Enums;

enum MaterialLoadPeriod: string
{
    case Day = 'DAY';
    case Week = 'WEEK';
    case Month = 'MONTH';
    case Year = 'YEAR';
    case AllTime = 'ALL_TIME';
}
