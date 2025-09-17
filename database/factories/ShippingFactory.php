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
        // $city = City::inRandomOrder()->first();

        return [
            'order_id' => Order::inRandomOrder()->first()->id,
            'address' => [
                'name' =>  fake()->name(),
                'phone' =>  fake()->phoneNumber(),
                // 'province' => $city->province->name,
                // 'city' => $city->name,
                'province' => fake()->city(),
                'city' => fake()->city(),
                'district' => fake()->city(),
                'zip_code' => fake()->postcode(),
                'address' => fake()->address()
            ],
            'courier' => fake()->randomElement(['jne', 'tiki', 'pos']),
            'courier_name' => fake()->company(),
            'service' => fake()->randomElement(['REG', 'YES', 'Pos Reguler', 'ECO']),
            'service_name' => fake()->randomElement(['Layanan Reguler', 'Yakin Esok Sampai', 'Economy Service']),
            'etd' => fake()->randomElement(['1-2 hari', '3-4 hari', '7 hari']),
            'weight' => fake()->numberBetween(700, 5000),
            'tracking_number' => fake()->unique()->isbn10(),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped',]),
        ];
    }
}
