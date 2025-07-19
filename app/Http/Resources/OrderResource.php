<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'items' => OrderItemResource::collection($this->items),
            'status' => $this->status,
            'total_amount' => $this->invoice->amount,
            'payment_due_date' => $this->invoice->due_date,
            'created_at' => $this->created_at
        ];
    }
}
