<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $address = FactoryData::address();

        return [
            'user_id' => User::factory(),
            'province_id' => $address['province_id'],
            'city_id' => $address['city_id'],
            'district_id' => $address['district_id'],
            'subdistrict_id' => $address['subdistrict_id'],
            'name' => $address['name'],
            'phone' => $address['phone'],
            'zip_code' => $address['zip_code'],
            'address' => $address['address'],
        ];
    }
}
