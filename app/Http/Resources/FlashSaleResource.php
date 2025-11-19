<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $products = $this->whenLoaded('products', function () {
            return $this->products->map(function ($product) {
                $product->flash_sale_status = $this->status;

                return $product;
            });
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_at' => optional($this->start_at)->format('Y-m-d H:i:s'),
            'end_at' => optional($this->end_at)->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'is_active' => (bool) $this->is_active,
            'products' => FlashSaleProductResource::collection($products),
        ];
    }
}
