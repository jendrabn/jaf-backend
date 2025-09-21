<?php

namespace App\Models;

use App\Traits\Auditable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Auditable;

    public const MEDIA_COLLECTION_NAME = 'product_images';

    public const SEX_SELECT = [
        1 => 'Male',
        2 => 'Female',
        3 => 'Unisex',
    ];

    protected $fillable = [
        'product_category_id',
        'product_brand_id',
        'name',
        'slug',
        'weight',
        'price',
        'stock',
        'description',
        'is_publish',
        'sex',
    ];

    protected $appends = [
        'image',
        'images',
        'is_wishlist',
        'sex_label',
        'rating_avg',
        'discount',
        'is_discounted',
        'discount_in_percent',
        'price_after_discount',
    ];

    protected $casts = [
        'sex' => 'integer',
        'is_publish' => 'boolean',
        'sold_count' => 'integer',
    ];

    protected $with = [
        'coupons',
        'category',
        'brand',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Crop, 100, 100)
            ->nonQueued();
    }

    public function images(): Attribute
    {
        return Attribute::get(function () {
            $files = $this->getMedia(self::MEDIA_COLLECTION_NAME);

            $files->each(function ($item) {
                $item->url = $item->getUrl();
                $item->preview = $item->getUrl('preview');
            });

            return $files;
        });
    }

    public function image(): Attribute
    {
        return Attribute::get(function () {
            $file = $this->getFirstMedia(self::MEDIA_COLLECTION_NAME);

            if ($file) {
                $file->url = $file->getUrl();
                $file->preview = $file->getUrl('preview');
            }

            return $file;
        });
    }

    public function isWishlist(): Attribute
    {
        return Attribute::get(fn() => false);
    }

    public function scopePublished()
    {
        return $this->where('is_publish', true);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(
            'sold_count',
            fn($q) => $q->withCount([
                'orderItems as sold_count' => fn($q) => $q
                    ->select(DB::raw('IFNULL(SUM(quantity), 0)'))
                    ->whereHas('order', fn($q) => $q->where('status', Order::STATUS_COMPLETED))
            ])
        );
    }

    public function sexLabel(): Attribute
    {
        return Attribute::get(fn() => $this->attributes['sex'] ? self::SEX_SELECT[$this->attributes['sex']] : '');
    }

    public function productRatings(): HasManyThrough
    {
        return $this->hasManyThrough(ProductRating::class, OrderItem::class, 'product_id', 'order_item_id');
    }

    public function ratingAvg(): Attribute
    {
        return Attribute::get(function () {
            $ratingAvg = $this->productRatings->avg('rating');
            $ratingAvg =  ceil($ratingAvg * 10) / 10;

            return $ratingAvg;
        });
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_product', 'product_id', 'coupon_id');
    }

    public function discount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->coupons->where('is_active', true)
                ->where('promo_type', 'product')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->sortByDesc('id')
                ->first()
        );
    }

    public function isDiscounted(): Attribute
    {
        return Attribute::get(fn() => $this->discount ? true : false);
    }

    public function discountInPercent(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->discount) {
                return 0;
            }

            if ($this->discount->discount_type === 'fixed') {
                return min(100, ($this->discount->discount_amount / $this->price) * 100);
            }

            return min(100, $this->discount->discount_amount);
        });
    }

    public function priceAfterDiscount(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->discount) {
                return $this->price;
            }

            if ($this->discount->discount_type === 'fixed') {
                return max(0, $this->price - $this->discount->discount_amount);
            }

            return max(0, $this->price - ($this->discount->discount_amount / 100) * $this->price);
        });
    }
}
