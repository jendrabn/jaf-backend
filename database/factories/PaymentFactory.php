<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Invoice;
use App\Models\Payment;
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
        $method = fake()->randomElement([
            Payment::METHOD_BANK,
            Payment::METHOD_EWALLET,
            Payment::METHOD_GATEWAY,
        ]);
        $bank = Bank::query()->inRandomOrder()->first();
        $bankInfo = $bank !== null
            ? [
                'name' => $bank->name,
                'code' => $bank->code,
                'account_name' => $bank->account_name,
                'account_number' => $bank->account_number,
            ]
            : FactoryData::paymentBank();
        $ewalletInfo = FactoryData::paymentEwallet();
        $info = match ($method) {
            Payment::METHOD_BANK => $bankInfo,
            Payment::METHOD_EWALLET => $ewalletInfo,
            default => [
                'provider' => 'midtrans',
                'channel' => fake()->randomElement(['qris', 'gopay', 'bank_transfer']),
                'reference' => 'MID-'.fake()->unique()->numerify('########'),
            ],
        };

        return [
            'invoice_id' => Invoice::factory(),
            'method' => $method,
            'info' => $info,
            'amount' => fake()->numberBetween(250000, 7500000),
            'status' => fake()->randomElement([Payment::STATUS_PENDING, Payment::STATUS_CANCELLED, Payment::STATUS_RELEASED]),
        ];
    }
}
