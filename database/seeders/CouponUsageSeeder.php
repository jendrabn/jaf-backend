<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use Illuminate\Database\Seeder;

class CouponUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (CouponUsage::query()->exists()) {
            return;
        }

        $coupons = Coupon::query()->get();

        if ($coupons->isEmpty()) {
            $this->call(CouponSeeder::class);
            $coupons = Coupon::query()->get();
        }

        $orders = Order::query()->get();

        if ($orders->isEmpty()) {
            $orders = Order::factory()
                ->count(6)
                ->create();
        }

        $orders
            ->shuffle()
            ->take(min(6, $orders->count()))
            ->values()
            ->each(function (Order $order, int $index) use ($coupons): void {
                $coupon = $coupons[$index % $coupons->count()];

                CouponUsage::query()->firstOrCreate([
                    'coupon_id' => $coupon->id,
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ]);
            });
    }
}
