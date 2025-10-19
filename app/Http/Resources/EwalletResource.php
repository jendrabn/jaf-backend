<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EwalletResource extends JsonResource
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
            'account_name' => $this->account_name,
            'account_username' => $this->account_username,
            'phone' => $this->phone,
            'logo' => $this->logo?->getUrl(),
        ];
    }
}
