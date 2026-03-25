<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipping>
 */
class ShippingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $address = FactoryData::address();
        $shipping = FactoryData::shipping();

        return [
            'order_id' => Order::factory(),
            'address' => [
                'name' => $address['name'],
                'phone' => $address['phone'],
                'province' => fake()->randomElement(['DKI Jakarta', 'Jawa Barat', 'DI Yogyakarta', 'Jawa Timur', 'Banten']),
                'city' => fake()->randomElement(['Jakarta Selatan', 'Bandung', 'Yogyakarta', 'Malang', 'Tangerang']),
                'district' => fake()->randomElement(['Cilandak', 'Coblong', 'Depok', 'Lowokwaru', 'Karawaci']),
                'subdistrict' => fake()->randomElement(['Lebak Bulus', 'Dago', 'Caturtunggal', 'Mojolangu', 'Cibodas']),
                'zip_code' => $address['zip_code'],
                'address' => $address['address'],
            ],
            'courier' => $shipping['courier'],
            'courier_name' => $shipping['courier_name'],
            'service' => $shipping['service'],
            'service_name' => $shipping['service_name'],
            'etd' => $shipping['etd'],
            'weight' => $shipping['weight'],
            'tracking_number' => $shipping['tracking_number'],
            'status' => $shipping['status'],
        ];
    }
}
