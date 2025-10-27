<?php

namespace App\Enums;

enum ContactMessageStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Spam => 'Spam',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'secondary',
            self::InProgress => 'warning',
            self::Resolved => 'success',
            self::Spam => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'mail',
            self::InProgress => 'hourglass-split',
            self::Resolved => 'check-circle',
            self::Spam => 'exclamation-triangle',
        };
    }
}
