<?php

namespace App\Models;

use App\Services\RajaOngkirService;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'user_id',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
        'name',
        'phone',
        'zip_code',
        'address',
    ];

    protected $appends = [
        'province',
        'city',
        'district',
        'subdistrict',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function province(): Attribute
    {
        $provinces = (new RajaOngkirService)->fetchProvinces();
        $province = collect($provinces)->where('id', '=', $this->province_id)->first();

        return Attribute::get(fn () => $province);
    }

    public function city(): Attribute
    {
        $cities = (new RajaOngkirService)->fetchCities($this->province_id);
        $city = collect($cities)->where('id', '=', $this->city_id)->first();

        return Attribute::get(fn () => $city);
    }

    public function district(): Attribute
    {
        $districts = (new RajaOngkirService)->fetchDistricts($this->city_id);
        $district = collect($districts)->where('id', '=', $this->district_id)->first();

        return Attribute::get(fn () => $district);
    }

    public function subdistrict(): Attribute
    {
        $subdistricts = (new RajaOngkirService)->fetchSubdistricts($this->district_id);
        $subdistrict = collect($subdistricts)->where('id', '=', $this->subdistrict_id)->first();

        return Attribute::get(fn () => $subdistrict);
    }
}
