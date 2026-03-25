<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Coupon::query()->exists()) {
            return;
        }

        Coupon::factory()
            ->count(8)
            ->create();
    }
}
