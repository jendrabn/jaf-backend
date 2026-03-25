<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CouponProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (CouponProduct::query()->exists()) {
            return;
        }

        $coupons = Coupon::query()
            ->where('promo_type', 'product')
            ->get();

        if ($coupons->isEmpty()) {
            $coupons = Coupon::factory()
                ->count(3)
                ->create([
                    'promo_type' => 'product',
                ]);
        }

        $products = Product::query()
            ->where('is_publish', true)
            ->get();

        if ($products->isEmpty()) {
            $products = collect();

            for ($index = 0; $index < 12; $index++) {
                $products->push(Product::factory()->create([
                    'is_publish' => true,
                ]));
            }
        }

        foreach ($coupons as $coupon) {
            $products
                ->shuffle()
                ->take(rand(2, 4))
                ->each(function (Product $product) use ($coupon): void {
                    CouponProduct::query()->firstOrCreate([
                        'coupon_id' => $coupon->id,
                        'product_id' => $product->id,
                    ]);
                });
        }
    }
}
