<?php

namespace App\Enums;

enum CampaignReceiptStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
    case Opened = 'opened';
    case Clicked = 'clicked';
}
