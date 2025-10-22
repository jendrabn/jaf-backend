<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case SENDING = 'sending';
    case SENT = 'sent';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENDING => 'Sending',
            self::SENT => 'Sent',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'badge-secondary',
            self::SENDING => 'badge-warning',
            self::SENT => 'badge-success',
        };
    }
}
