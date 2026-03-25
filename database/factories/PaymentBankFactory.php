<?php

namespace Database\Factories;

use App\Models\Payment;
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
        $bank = FactoryData::paymentBank();

        return [
            'payment_id' => Payment::factory()->state([
                'method' => 'bank',
                'info' => $bank,
            ]),
            'name' => $bank['name'],
            'account_name' => $bank['account_name'],
            'account_number' => $bank['account_number'],
        ];
    }
}
