<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'promo_type' => 'period', // overridden in tests when needed
            'code' => strtoupper($this->faker->bothify('CPN###??')),
            'discount_type' => 'fixed',
            'discount_amount' => $this->faker->numberBetween(1000, 10000),
            'limit' => null,
            'limit_per_user' => null,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ];
    }
}
