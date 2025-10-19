<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bank = Bank::inRandomOrder()->first();

        return [
            'invoice_id' => Invoice::inRandomOrder()->first()->id,
            'method' => 'bank',
            'info' => [
                'name' => $bank->name,
                'code' => $bank->code,
                'account_name' => $bank->account_name,
                'account_number' => $bank->account_number,
            ],
            'amount' => fake()->numberBetween(150000, 1000000),
            'status' => fake()->randomElement(['pending', 'cancelled', 'realeased']),
        ];
    }
}
