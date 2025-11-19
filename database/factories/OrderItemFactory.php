<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::inRandomOrder()->first()->id,
            'product_id' => Product::inRandomOrder()->first()->id,
            'flash_sale_id' => null,
            'name' => fake()->sentence(),
            'weight' => fake()->numberBetween(1000, 5000),
            'price' => fake()->numberBetween(50000, 1000000),
            'quantity' => fake()->numberBetween(1, 20),
        ];
    }
}
