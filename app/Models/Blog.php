<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Blog extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Auditable;

    const MEDIA_COLLECTION_NAME = 'blog_images';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'min_read',
        'featured_image_description',
        'views_count',
        'is_publish',
        'blog_category_id',
        'user_id'
    ];

    protected $casts = [
        'is_publish' => 'boolean',
    ];

    protected $appends = [
        'featured_image',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id', 'id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogTag::class,
            'blog_tag_blog',
            'blog_id',
            'blog_tag_id',
        );
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Crop, 100, 100)
            ->nonQueued();
    }

    public function featuredImage(): Attribute
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

    public function scopePublished($query)
    {
        return $query->where('is_publish', true);
    }
}
