<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRating extends Model
{
    use Auditable;

    protected $fillable = [
        'order_item_id',
        'rating',
        'comment',
        'is_anonymous',
        'is_publish',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
