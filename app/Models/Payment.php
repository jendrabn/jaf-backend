<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory, Auditable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RELEASED = 'realeased';

    public const STATUSES = [
        self::STATUS_PENDING => ['label' => 'Pending'],
        self::STATUS_CANCELLED => ['label' => 'Cancelled'],
        self::STATUS_RELEASED => ['label' => 'Realeased'],
    ];

    public const METHOD_BANK = 'bank';
    public const METHOD_EWALLET = 'ewallet';

    protected $fillable = [
        'invoice_id',
        'method',
        'info',
        'amount',
        'status',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function bank(): HasOne
    {
        return $this->hasOne(PaymentBank::class);
    }

    public function ewallet(): HasOne
    {
        return $this->hasOne(PaymentEwallet::class);
    }

    public function info(): Attribute
    {
        return Attribute::make(
            set: fn($value) => json_encode($value),
            get: fn($value) => json_decode($value, true)
        );
    }
}
