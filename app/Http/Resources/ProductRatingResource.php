<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductRatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userName = $this->orderItem->order->user->name;

        return [
            'order_item_id' => $this->order_item_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_anonymous' => $this->is_anonymous,
            'created_at' => $this->created_at,
            'username' => $this->is_anonymous ? Str::mask($userName, '*', 1, -1) : $userName,
        ];
    }
}
