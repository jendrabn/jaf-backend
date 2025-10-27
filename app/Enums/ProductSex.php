<?php

namespace App\Enums;

enum ProductSex: int
{
    case Male = 1;
    case Female = 2;
    case Unisex = 3;

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
            self::Unisex => 'Unisex',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Male => 'primary',
            self::Female => 'danger',
            self::Unisex => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Male => 'mars',
            self::Female => 'venus',
            self::Unisex => 'genderless',
        };
    }
}
