<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $productData = (new ProductResource($this))->toArray($request);

        $pivot = $this->pivot;
        $flashStock = (int) ($pivot->stock_flash ?? 0);
        $sold = (int) ($pivot->sold ?? 0);

        return array_merge($productData, [
            'flash_price' => isset($pivot->flash_price) ? (float) $pivot->flash_price : null,
            'flash_stock' => $flashStock,
            'flash_sold' => $sold,
            'flash_stock_remaining' => max(0, $flashStock - $sold),
            'max_qty_per_user' => (int) ($pivot->max_qty_per_user ?? 0),
        ]);
    }
}
