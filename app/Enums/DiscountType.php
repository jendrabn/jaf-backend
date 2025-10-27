<?php

namespace App\Enums;

enum DiscountType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed Amount',
            self::Percentage => 'Percentage',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Fixed => 'primary',
            self::Percentage => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Fixed => 'dollar-sign',
            self::Percentage => 'percent',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Fixed => '$',
            self::Percentage => '%',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Fixed => 'A fixed amount discount applied to the total price.',
            self::Percentage => 'A percentage-based discount applied to the total price.',
        };
    }
}
