<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Pending = 'pending';
    case Processing = 'processing';
    case OnDelivery = 'on_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending Payment',
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::OnDelivery => 'On Delivery',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment => 'secondary',
            self::Pending => 'warning',
            self::Processing => 'success',
            self::OnDelivery => 'success',
            self::Completed => 'info',
            self::Cancelled => 'danger',
        };
    }
}
