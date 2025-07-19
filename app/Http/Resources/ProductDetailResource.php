<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ProductDetailResource extends JsonResource
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
            'images' => $this->images->map(fn($media) => $media?->getUrl())->toArray(),
            'category' => ProductCategoryResource::make($this->category),
            'description' => $this->description,
            'brand' => $this->whenNotNull(ProductBrandResource::make($this->brand)),
            'sex' => $this->sex,
            'price' => $this->price,
            'stock' => $this->stock,
            'weight' => $this->weight,
            'sold_count' => $this->sold_count,
            'is_wishlist' => $this->is_wishlist,
            'rating_avg' => $this->ratingAvg ?? 0,
            'ratings' => ProductRatingResource::collection($this->productRatings),
        ];
    }
}
