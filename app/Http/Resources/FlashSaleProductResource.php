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
        $flashPrice = isset($pivot->flash_price) ? (float) $pivot->flash_price : null;
        $shouldMaskFlashPrice = $this->shouldMaskFlashPrice();

        return array_merge($productData, [
            'flash_price' => $flashPrice,
            'flash_price_display' => $shouldMaskFlashPrice
                ? $this->maskPrice($flashPrice)
                : $this->formatPrice($flashPrice),
            'flash_stock' => $flashStock,
            'flash_sold' => $sold,
            'flash_stock_remaining' => max(0, $flashStock - $sold),
            'max_qty_per_user' => (int) ($pivot->max_qty_per_user ?? 0),
            'is_flash_price_masked' => $shouldMaskFlashPrice,
        ]);
    }

    private function shouldMaskFlashPrice(): bool
    {
        return ($this->resource->flash_sale_status ?? null) === 'scheduled';
    }

    private function formatPrice(?float $price): ?string
    {
        if ($price === null) {
            return null;
        }

        return number_format($price, 0, ',', '.');
    }

    private function maskPrice(?float $price): ?string
    {
        $formatted = $this->formatPrice($price);

        if ($formatted === null) {
            return null;
        }

        return preg_replace('/\d/', '?', $formatted, 1);
    }
}
