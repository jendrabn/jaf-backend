<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Ewallet extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Auditable;

    const MEDIA_COLLECTION_NAME = 'ewallet_images';

    public const EWALLET_SELECT = [
        'GoPay',
        'OVO',
        'ShopeePay',
        'Dana',
    ];

    protected $fillable = [
        'name',
        'account_name',
        'account_username',
        'phone',
    ];

    protected $appends = [
        'logo',
    ];

    public function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaCollections(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Crop, 100, 100)
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
