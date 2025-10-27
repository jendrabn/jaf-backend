<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Bank = 'bank';
    case Ewallet = 'ewallet';
    case Gateway = 'gateway';

    public function label(): string
    {
        return match ($this) {
            self::Bank => 'Bank Transfer',
            self::Ewallet => 'E-wallet',
            self::Gateway => 'Payment Gateway',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Bank => 'primary',
            self::Ewallet => 'info',
            self::Gateway => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Bank => 'building',
            self::Ewallet => 'wallet',
            self::Gateway => 'credit-card',
        };
    }
}
