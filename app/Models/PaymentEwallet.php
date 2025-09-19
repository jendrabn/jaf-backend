<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentEwallet extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'payment_id',
        'name',
        'account_name',
        'account_username',
        'phone'
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
