<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\CouponProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CouponProduct>
 */
class CouponProductFactory extends Factory
{
    protected $model = CouponProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory()->state([
                'promo_type' => 'product',
                'discount_type' => fake()->randomElement(['fixed', 'percentage']),
            ]),
            'product_id' => Product::factory()->state(['is_publish' => true]),
        ];
    }
}
