<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case TRANSACTION = 'transaction';
    case ACCOUNT = 'account';
    case PROMO = 'promo';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match ($this) {
            self::TRANSACTION => 'Transaction',
            self::ACCOUNT => 'Account',
            self::PROMO => 'Promo',
            self::SYSTEM => 'System',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TRANSACTION => 'primary',
            self::ACCOUNT => 'info',
            self::PROMO => 'success',
            self::SYSTEM => 'secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TRANSACTION => 'fas fa-shopping-cart',
            self::ACCOUNT => 'fas fa-user',
            self::PROMO => 'fas fa-gift',
            self::SYSTEM => 'fas fa-cog',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
            'icon' => $case->icon(),
        ], self::cases());
    }
}
