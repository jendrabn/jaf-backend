<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => ProductResource::make($this->product),
            'name' => $this->name,
            'price' => $this->price,
            'weight' => $this->weight,
            'quantity' => $this->quantity,
            'rating' => $this->whenNotNull(ProductRatingResource::make($this->rating)),
        ];
    }
}
