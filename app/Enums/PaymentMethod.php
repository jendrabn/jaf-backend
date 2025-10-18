<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Bank = 'bank';
    case Ewallet = 'ewallet';
    case Gateway = 'gateway';
}
