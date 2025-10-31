<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use Auditable, HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_ON_DELIVERY = 'on_delivery';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'total_price',
        'discount',
        'discount_name',
        'tax_amount',
        'tax_name',
        'shipping_cost',
        'gateway_fee',
        'gateway_fee_name',
        'note',
        'cancel_reason',
        'status',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];

    protected $appends = [
        'total_quantity',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(Shipping::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class, 'meta->order_id');
    }

    public function totalQuantity(): Attribute
    {
        return Attribute::get(fn () => $this->items->reduce(fn ($carry, $item) => $carry + $item->quantity));
    }
}
