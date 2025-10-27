<?php

namespace App\Enums;

enum SubscriberStatus: string
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Subscribed => 'Subscribed',
            self::Unsubscribed => 'Unsubscribed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Subscribed => 'success',
            self::Unsubscribed => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'hourglass-split',
            self::Subscribed => 'check-circle',
            self::Unsubscribed => 'x-circle',
        };
    }
}
