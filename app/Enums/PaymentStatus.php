<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Realeased = 'realeased';
}
