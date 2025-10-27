<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case SENDING = 'sending';
    case SENT = 'sent';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENDING => 'Sending',
            self::SENT => 'Sent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::SENDING => 'warning',
            self::SENT => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'pencil-square',
            self::SENDING => 'send',
            self::SENT => 'check-circle',
        };
    }
}
