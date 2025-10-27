<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Realeased = 'realeased';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Cancelled => 'Cancelled',
            self::Realeased => 'Realeased',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Cancelled => 'danger',
            self::Realeased => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'hourglass-split',
            self::Cancelled => 'x-circle',
            self::Realeased => 'check-circle',
        };
    }
}
