<?php

namespace App\Enums;

enum PromoType: string
{
    case Limit = 'limit';
    case Period = 'period';
    case Product = 'product';

    public function label(): string
    {
        return match ($this) {
            self::Limit => 'Limit',
            self::Period => 'Period',
            self::Product => 'Product',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Limit => 'info',
            self::Period => 'primary',
            self::Product => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Limit => 'gauge',
            self::Period => 'calendar',
            self::Product => 'box-seam',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Limit => 'A promotion based on a limited quantity or usage.',
            self::Period => 'A promotion valid for a specific time period.',
            self::Product => 'A promotion applied to specific products and time periods.',
        };
    }
}
