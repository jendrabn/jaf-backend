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
        $ewallet = FactoryData::paymentEwallet();

        return [
            'payment_id' => Payment::factory()->state([
                'method' => 'ewallet',
                'info' => $ewallet,
            ]),
            'name' => $ewallet['name'] ?? fake()->randomElement(Ewallet::EWALLET_SELECT),
            'account_name' => $ewallet['account_name'],
            'account_username' => $ewallet['account_username'],
            'phone' => $ewallet['phone'],
        ];
    }
}
