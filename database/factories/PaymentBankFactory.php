<?php

namespace Database\Factories;

use App\Models\PaymentBank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentBank>
 */
class PaymentBankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => function () {
                return PaymentBank::inRandomOrder()->first()->id;
            },
            'name' => fake()->randomElement(['BCA', 'BSI', 'Mandiri', 'BRI', 'BNI']),
            'account_name' => fake()->name(),
            'account_number' => fake()->creditCardNumber(),
        ];
    }
}
