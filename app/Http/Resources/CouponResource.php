<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
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
            'description' => $this->description,
            'promo_type' => $this->promo_type,
            'code' => $this->code,
            'discount_type' => $this->discount_type,
            'discount_amount' => $this->discount_amount,
            'limit' => $this->limit,
            'limit_per_user' => $this->limit_per_user,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'available_coupons' => $this->available_coupons,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
