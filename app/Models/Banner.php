<?php

namespace App\Models;

use App\Traits\Auditable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Auditable;

    public const MEDIA_COLLECTION_NAME = 'banner_images';

    protected $fillable = [
        'image_description',
        'url',
    ];

    protected $appends = [
        'image',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Crop, 100, 100)
            ->nonQueued();
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
}
