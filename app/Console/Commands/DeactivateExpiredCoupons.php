<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;

class DeactivateExpiredCoupons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deactivate-expired-coupons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Inactive if limit
        $limitCoupons = Coupon::withCount('usages')
            ->active()
            ->where('promo_type', 'limit')
            ->get();

        $limitCoupons->map(function ($coupon) {
            if ($coupon->usages_count >= $coupon->limit) {
                $coupon->update([
                    'is_active' => 0,
                ]);
            }
        });

        // Inactive if expired period
        Coupon::active()
            ->whereIn('promo_type', ['period', 'product'])
            ->where('end_date', '<', now())
            ->update([
                'is_active' => 0,
            ]);
    }
}
