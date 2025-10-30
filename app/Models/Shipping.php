<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use Auditable, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const MAX_WEIGHT = 25000;

    public const COURIERS = ['jne', 'tiki', 'pos'];

    protected $fillable = [
        'order_id',
        'address',
        'courier',
        'courier_name',
        'service',
        'service_name',
        'etd',
        'weight',
        'tracking_number',
        'status',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function address(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => json_encode($value),
            get: fn ($value) => json_decode($value, true)
        );
    }
}
