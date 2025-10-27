<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Paid = 'paid';
    case Unpaid = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::Unpaid => 'Unpaid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Unpaid => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Paid => 'check-circle',
            self::Unpaid => 'x-circle',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }
}
