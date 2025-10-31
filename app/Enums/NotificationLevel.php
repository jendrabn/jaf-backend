<?php

namespace App\Enums;

enum NotificationLevel: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case SUCCESS = 'success';
    case ERROR = 'error';

    public function label(): string
    {
        return match ($this) {
            self::INFO => 'Info',
            self::WARNING => 'Warning',
            self::SUCCESS => 'Success',
            self::ERROR => 'Error',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INFO => 'info',
            self::WARNING => 'warning',
            self::SUCCESS => 'success',
            self::ERROR => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::INFO => 'fas fa-info-circle',
            self::WARNING => 'fas fa-exclamation-triangle',
            self::SUCCESS => 'fas fa-check-circle',
            self::ERROR => 'fas fa-times-circle',
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
