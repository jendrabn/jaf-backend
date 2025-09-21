<?php

namespace App\Enums;

enum PromoType: string
{
    case Limit = 'limit';
    case Period = 'period';
    case Product = 'product';
}
