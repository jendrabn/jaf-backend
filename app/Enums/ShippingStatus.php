<?php

namespace App\Enums;

enum ShippingStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
}
