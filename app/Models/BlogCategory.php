<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'blog_category_id', 'id');
    }
}
