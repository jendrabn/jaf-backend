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
        $price = fake()->numberBetween(180000, 4500000);
        $discountInPercent = fake()->randomElement([0, 0, 5, 10, 15, 20]);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'flash_sale_id' => null,
            'name' => fake()->randomElement([
                'Dior Sauvage EDP 60ml',
                'YSL Libre EDP 90ml',
                'JAF Atelier Citrus Vetiver EDP 50ml',
                'JAF Home Amber Santal Room Spray 150ml',
            ]),
            'weight' => fake()->numberBetween(220, 980),
            'price' => $price,
            'discount_in_percent' => $discountInPercent,
            'price_after_discount' => (int) round($price - (($discountInPercent / 100) * $price)),
            'quantity' => fake()->numberBetween(1, 4),
        ];
    }
}
