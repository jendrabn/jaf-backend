<?php

namespace Database\Factories;

use App\Models\Ewallet;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentEwallet>
 */
class PaymentEwalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::inRandomOrder()->first()->id,
            'name' => fake()->randomElement(Ewallet::EWALLET_SELECT),
            'account_name' => fake()->name,
            'account_username' => fake()->userName,
            'phone' => fake()->phoneNumber,
        ];
    }
}
