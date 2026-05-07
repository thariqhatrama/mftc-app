<?php

namespace App\Enums;

enum ScopeObject: string
{
    case HOTEL = 'hotel';
    case RESTAURANT = 'restaurant';
    case TRAVEL = 'travel';
    case RETAIL = 'retail';
    case AREA = 'area';
    case TERMINAL = 'terminal';
    case HEALTH_THERAPY = 'health_therapy';
    case MICE = 'mice';
    case SWIMMING_POOL = 'swimming_pool';
    case HOSPITAL = 'hospital';
}
