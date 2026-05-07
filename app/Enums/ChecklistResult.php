<?php

namespace App\Enums;

enum ChecklistResult: string
{
    case COMPLIANT = 'compliant';
    case NON_COMPLIANT = 'non_compliant';
    case NA = 'na';
}
