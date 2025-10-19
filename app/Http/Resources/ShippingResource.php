<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingResource extends JsonResource
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
            'courir' => $this->courir,
            'courier_name' => $this->courier_name,
            'service' => $this->service,
            'service_name' => $this->service_name,
            'etd' => $this->etd,
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
        ];
    }
}
