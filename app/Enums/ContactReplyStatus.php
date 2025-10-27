<?php

namespace App\Enums;

enum ContactReplyStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Failed => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Sent => 'success',
            self::Failed => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'pencil-square',
            self::Sent => 'envelope-paper',
            self::Failed => 'x-circle',
        };
    }
}
