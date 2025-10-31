<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPassword, HasMedia
{
    use Auditable, HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    public const MEDIA_COLLECTION_NAME = 'avatar_images';

    public const SEX_SELECT = [
        1 => 'Male',
        2 => 'Female',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'sex',
        'birth_date',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'sex' => 'integer',
    ];

    protected $appends = [
        'sex_label',
        'avatar',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function address(): HasOne
    {
        return $this->hasOne(UserAddress::class, 'user_id', 'id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'id');
    }

    public function sexLabel(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => self::SEX_SELECT[$attributes['sex'] ?? null] ?? ''
        );
    }

    public function delete(): void
    {
        if ((int) $this->id === 1) {
            throw new \Exception('Cannot delete record with id = 1');
        }

        parent::delete();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Crop, 120, 120)
            ->nonQueued();
    }

    public function avatar(): Attribute
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

    // Normalize phone number before saving to database
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => normalizePhoneNumber($value),
        );
    }
}
