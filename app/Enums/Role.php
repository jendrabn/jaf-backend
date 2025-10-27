<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::USER => 'primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ADMIN => 'shield-lock',
            self::USER => 'person-circle',
        };
    }
}
