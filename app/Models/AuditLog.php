<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public $table = 'audit_logs';

    protected $fillable = [
        'description',
        'event',
        'subject_id',
        'subject_type',
        'user_id',
        'before',
        'after',
        'changed',
        'meta',
        'properties',   // legacy
        'host',         // legacy ip/host
        'url',
        'method',
        'ip',
        'user_agent',
        'request_id',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'changed' => 'array',
        'meta' => 'array',
        'properties' => 'array', // legacy
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
