<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use Auditable;

    protected $fillable = [
        'id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
