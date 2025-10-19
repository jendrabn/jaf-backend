<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'promo_type',
        'code',
        'discount_type',
        'discount_amount',
        'limit',
        'limit_per_user',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'available_coupons',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product', 'coupon_id', 'product_id');
    }

    public function availableCoupons(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->limit ? $this->limit - $this->usages_count : null,
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function code(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => str_replace(' ', '', $value),
        );
    }
}
