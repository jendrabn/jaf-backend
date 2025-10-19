<?php

namespace Database\Factories;

use App\Models\Invoice;
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
        return [
            'order_id' => Order::inRandomOrder()->first()->id ?? Order::factory()->create()->id,
            'number' => 'INV/'.fake()->date('Ymd').'/'.fake()->randomDigit(),
            'amount' => fake()->numberBetween(150000, 1000000),
            'status' => fake()->randomElement([Invoice::STATUS_PAID, Invoice::STATUS_UNPAID]),
            'due_date' => fake()->dateTime(),
        ];
    }
}
