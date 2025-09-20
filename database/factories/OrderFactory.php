<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'total_price' => fake()->numberBetween(100000, 1000000),
            'discount' => fake()->numberBetween(0, 50000),
            'shipping_cost' => fake()->numberBetween(5000, 50000),
            'note' => fake()->text(),
            'cancel_reason' => '',
            'status' => fake()->randomElement([
                'pending_payment',
                'pending',
                'processing',
                'on_delivery',
                'completed',
                'cancelled'
            ]),
            'confirmed_at' => fake()->dateTime(),
            'cancelled_at' => fake()->dateTime(),
            'completed_at' => fake()->dateTime(),
            'created_at' => fake()->dateTime(),
        ];
    }
}
