<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use Auditable, HasFactory, InteractsWithMedia;

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
        'sku',
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
        'flash_sale_price',
        'is_in_flash_sale',
        'final_price',
        'flash_sale_end_at',
    ];

    protected $casts = [
        'sex' => 'integer',
        'is_publish' => 'boolean',
        'sold_count' => 'integer',
        'is_active' => 'boolean',
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
        return Attribute::get(fn () => false);
    }

    public function scopePublished()
    {
        return $this->where('is_publish', true);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(
            'sold_count',
            fn ($q) => $q->withCount([
                'orderItems as sold_count' => fn ($q) => $q
                    ->select(DB::raw('IFNULL(SUM(quantity), 0)'))
                    ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED)),
            ])
        );

        // Ensure SKU is generated on create when not provided
        static::creating(function (self $product): void {
            if (! filled($product->sku)) {
                // Trigger the mutator to sanitize/generate later
                $product->sku = '';
            }
        });

        // Ensure SKU is generated on save when left empty (and keep existing if already set)
        static::saving(function (self $product): void {
            if (! filled($product->sku)) {
                $product->sku = self::generateSkuForModel($product);
            }
        });
    }

    public function sexLabel(): Attribute
    {
        return Attribute::get(function () {
            $sex = $this->attributes['sex'] ?? null;

            return $sex !== null && isset(self::SEX_SELECT[$sex])
                ? self::SEX_SELECT[$sex]
                : '';
        });
    }

    public function productRatings(): HasManyThrough
    {
        return $this->hasManyThrough(ProductRating::class, OrderItem::class, 'product_id', 'order_item_id');
    }

    public function ratingAvg(): Attribute
    {
        return Attribute::get(function () {
            $ratingAvg = $this->productRatings->avg('rating');
            $ratingAvg = ceil($ratingAvg * 10) / 10;

            return $ratingAvg;
        });
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_product', 'product_id', 'coupon_id');
    }

    public function flashSales(): BelongsToMany
    {
        return $this->belongsToMany(FlashSale::class, 'flash_sale_products')
            ->withPivot([
                'flash_price',
                'stock_flash',
                'sold',
                'max_qty_per_user',
            ])
            ->withTimestamps();
    }

    public function discount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->coupons->where('is_active', true)
                ->where('promo_type', 'product')
                ->where(fn ($coupon) => $coupon->start_date <= now() && $coupon->end_date >= now())
                ->sortByDesc('id')
                ->first()
        );
    }

    public function isDiscounted(): Attribute
    {
        return Attribute::get(fn () => $this->discount ? true : false);
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

    public function flashSalePrice(): Attribute
    {
        return Attribute::get(function () {
            $flashSale = $this->activeFlashSale();

            return $flashSale
                ? (float) $flashSale->pivot->flash_price
                : null;
        });
    }

    public function isInFlashSale(): Attribute
    {
        return Attribute::get(fn () => (bool) $this->activeFlashSale());
    }

    public function finalPrice(): Attribute
    {
        return Attribute::get(function () {
            $flashSale = $this->activeFlashSale();

            if ($flashSale) {
                return (float) $flashSale->pivot->flash_price;
            }

            return $this->priceAfterDiscount;
        });
    }

    public function flashSaleEndAt(): Attribute
    {
        return Attribute::get(function () {
            $flashSale = $this->activeFlashSale();

            return $flashSale ? $flashSale->end_at : null;
        });
    }

    private function activeFlashSale(): ?FlashSale
    {
        return $this->flashSales()
            ->runningNow()
            ->orderByDesc('end_at')
            ->first();
    }

    public function sku(): Attribute
    {
        return Attribute::make(
            set: function ($value, array $attributes) {
                $value = is_string($value) ? trim($value) : '';

                if ($value !== '') {
                    $sanitized = strtoupper(preg_replace('/[^A-Za-z0-9\-_]/', '', $value));

                    return $sanitized !== '' ? mb_substr($sanitized, 0, 50) : null;
                }

                // Leave empty here; it will be generated deterministically in saving() hook
                return null;
            }
        );
    }

    private static function generateSkuFromAttributes(array $attributes): string
    {
        $brandName = null;
        if (! empty($attributes['product_brand_id'])) {
            $brand = ProductBrand::find($attributes['product_brand_id']);
            $brandName = $brand?->name;
        }

        $categoryName = null;
        if (! empty($attributes['product_category_id'])) {
            $cat = ProductCategory::find($attributes['product_category_id']);
            $categoryName = $cat?->name;
        }

        $brandCode = self::alphaNumUpper($brandName ?? '');
        $brandCode = $brandCode !== '' ? mb_substr($brandCode, 0, 3) : 'NON';

        $catCode = self::alphaNumUpper($categoryName ?? '');
        $catCode = $catCode !== '' ? mb_substr($catCode, 0, 3) : 'GEN';

        $nameRaw = (string) ($attributes['name'] ?? '');
        $nameCode = self::nameCode($nameRaw);

        $sex = $attributes['sex'] ?? null;
        $sexCode = self::sexCode(is_numeric($sex) ? (int) $sex : null);

        $seq = self::nextSequenceFor(
            $attributes['product_brand_id'] ?? null,
            $attributes['product_category_id'] ?? null,
            $brandCode,
            $catCode
        );

        $sku = sprintf('%s-%s-%s-%s-%s', $brandCode, $catCode, $nameCode, $sexCode, $seq);

        return mb_substr($sku, 0, 50);
    }

    private static function generateSkuForModel(self $p): string
    {
        $brandName = null;
        if (! empty($p->product_brand_id)) {
            $brand = ProductBrand::find($p->product_brand_id);
            $brandName = $brand?->name;
        }

        $categoryName = null;
        if (! empty($p->product_category_id)) {
            $cat = ProductCategory::find($p->product_category_id);
            $categoryName = $cat?->name;
        }

        $brandCode = self::alphaNumUpper($brandName ?? '');
        $brandCode = $brandCode !== '' ? mb_substr($brandCode, 0, 3) : 'NON';

        $catCode = self::alphaNumUpper($categoryName ?? '');
        $catCode = $catCode !== '' ? mb_substr($catCode, 0, 3) : 'GEN';

        $nameRaw = (string) ($p->name ?? '');
        $nameCode = self::nameCode($nameRaw);

        $sexCode = self::sexCode($p->sex);

        $seq = self::nextSequenceFor(
            $p->product_brand_id ?? null,
            $p->product_category_id ?? null,
            $brandCode,
            $catCode
        );

        $sku = sprintf('%s-%s-%s-%s-%s', $brandCode, $catCode, $nameCode, $sexCode, $seq);

        return mb_substr($sku, 0, 50);
    }

    private static function alphaNumUpper(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9]/', '', $value);

        return strtoupper($value ?? '');
    }

    private static function nameCode(string $name): string
    {
        // Use slug (deterministic), remove hyphens, then take first 4 chars.
        $slug = Str::slug($name);
        $compact = strtoupper(str_replace('-', '', $slug));

        if ($compact === '') {
            $compact = 'PROD';
        }

        $code = mb_substr($compact, 0, 4);

        // Pad to 4 if shorter
        return str_pad($code, 4, 'X');
    }

    private static function sexCode(?int $sex): string
    {
        return match ($sex) {
            1 => 'M',
            2 => 'F',
            default => 'U',
        };
    }

    private static function nextSequenceFor(?int $brandId, ?int $categoryId, string $brandCode, string $catCode): string
    {
        $prefix = $brandCode.'-'.$catCode.'-';

        $query = static::query()
            ->select('sku')
            ->whereNotNull('sku')
            ->where('sku', 'like', $prefix.'%');

        if ($brandId) {
            $query->where('product_brand_id', $brandId);
        } else {
            $query->whereNull('product_brand_id');
        }

        if ($categoryId) {
            $query->where('product_category_id', $categoryId);
        } else {
            $query->whereNull('product_category_id');
        }

        $max = 0;
        foreach ($query->pluck('sku') as $sku) {
            if (preg_match('/-(\d{4})$/', (string) $sku, $m)) {
                $num = (int) $m[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $next = $max + 1;

        return str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
