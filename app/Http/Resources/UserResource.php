<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'sex' => $this->sex,
            'birth_date' => $this->birth_date,
            'auth_token' => $this->whenNotNull($this->auth_token),
            'avatar' => $this->avatar->url ?? asset('images/default-profile.jpg'),
            // OTP flow fields (present for password login when OTP is required)
            'otp_required' => $this->whenNotNull($this->otp_required, fn() => (bool) $this->otp_required),
            'otp_expires_at' => $this->whenNotNull($this->otp_expires_at, fn() => $this->otp_expires_at instanceof \DateTimeInterface ? $this->otp_expires_at->format('d-m-Y H:i:s') : $this->otp_expires_at),
            'otp_sent_to' => $this->whenNotNull($this->otp_sent_to),
        ];
    }
}
