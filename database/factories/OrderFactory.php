<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ]);
        $createdAt = CarbonImmutable::instance(fake()->dateTimeBetween('-60 days', '-1 day'));
        $confirmedAt = in_array($status, [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_COMPLETED,
        ], true) ? $createdAt->addHours(fake()->numberBetween(1, 24)) : null;
        $cancelledAt = $status === Order::STATUS_CANCELLED
            ? $createdAt->addHours(fake()->numberBetween(1, 48))
            : null;
        $completedAt = $status === Order::STATUS_COMPLETED
            ? $createdAt->addDays(fake()->numberBetween(2, 10))
            : null;

        return [
            'user_id' => User::factory(),
            'total_price' => fake()->numberBetween(250000, 6500000),
            'discount' => fake()->randomElement([0, 0, 0, 25000, 50000, 75000, 150000]),
            'discount_name' => fake()->optional(0.4)->randomElement(['Weekend Fragrance Picks', 'Luxury Scent Voucher', 'Member Exclusive']),
            'tax_amount' => fake()->randomElement([0, 0, 11000, 22000, 33000]),
            'tax_name' => fake()->optional(0.5)->randomElement(['PPN', 'PPN Produk Wewangian']),
            'shipping_cost' => fake()->numberBetween(18000, 45000),
            'gateway_fee' => fake()->randomElement([0, 0, 2500, 4000]),
            'gateway_fee_name' => fake()->optional(0.3)->randomElement(['Midtrans Flat Fee', 'Payment Gateway Fee']),
            'note' => fake()->optional(0.7)->randomElement([
                'Tolong pastikan botol dibungkus bubble wrap ekstra.',
                'Kirim setelah pukul 13.00 karena alamat kantor.',
                'Sertakan gift note sederhana tanpa harga.',
            ]),
            'cancel_reason' => $status === Order::STATUS_CANCELLED
                ? fake()->randomElement(['Pembayaran melewati batas waktu.', 'Pelanggan mengubah pilihan parfum.', 'Stok varian yang dipilih habis.'])
                : null,
            'status' => $status,
            'confirmed_at' => $confirmedAt,
            'cancelled_at' => $cancelledAt,
            'completed_at' => $completedAt,
            'created_at' => $createdAt,
            'updated_at' => $completedAt ?? $cancelledAt ?? $confirmedAt ?? $createdAt,
        ];
    }
}
