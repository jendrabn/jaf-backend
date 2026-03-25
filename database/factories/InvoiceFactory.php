<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['paid', 'unpaid']);

        return [
            'order_id' => Order::factory(),
            'number' => 'INV/'.now()->format('Ymd').'/'.fake()->unique()->numerify('######'),
            'amount' => fake()->numberBetween(250000, 7500000),
            'status' => $status,
            'due_date' => $status === 'paid'
                ? fake()->dateTimeBetween('-30 days', '-1 day')
                : fake()->dateTimeBetween('now', '+7 days'),
        ];
    }
}
