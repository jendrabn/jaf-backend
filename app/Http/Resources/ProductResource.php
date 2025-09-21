<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image->url ?? asset('images/default-product.jpg'),
            'category' => ProductCategoryResource::make($this->category),
            'brand' => $this->whenNotNull(ProductBrandResource::make($this->brand)),
            'sex' => $this->sex,
            'price' => $this->price,
            'stock' => $this->stock,
            'weight' => $this->weight,
            'sold_count' => $this->sold_count ?? 0,
            'is_wishlist' => $this->is_wishlist ?? false,
            'rating_avg' => $this->ratingAvg ?? 0,
            'discount' => $this->discount,
            'is_discounted' => $this->is_discounted,
            'discount_in_percent' => $this->discount_in_percent,
            'price_after_discount' => $this->price_after_discount,
        ];
    }
}
