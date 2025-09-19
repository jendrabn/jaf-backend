<?php

namespace App\Models;

use App\Traits\Auditable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductBrand extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Auditable;

    const MEDIA_COLLECTION_NAME = 'product_brand_images';

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $appends = [
        'logo'
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_brand_id', 'id');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Crop, 120, 120)
            ->nonQueued();
    }

    public function logo(): Attribute
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
}
