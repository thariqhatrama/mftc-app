<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PENDING_APPROVAL = 'pending_approval';
    case PAID = 'paid';
    case EXPIRED = 'expired';
}
