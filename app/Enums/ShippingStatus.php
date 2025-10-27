<?php

namespace App\Enums;

enum ShippingStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'info',
            self::Shipped => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'hourglass-split',
            self::Processing => 'gear',
            self::Shipped => 'truck',
        };
    }

    public function isShipped(): bool
    {
        return $this === self::Shipped;
    }
}
