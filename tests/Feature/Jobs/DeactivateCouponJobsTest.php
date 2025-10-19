<?php

namespace Tests\Feature\Jobs;

use App\Jobs\DeactivateCouponIfExpired;
use App\Jobs\DeactivateCouponIfLimitReached;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeactivateCouponJobsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deactivates_period_coupon_after_end_date(): void
    {
        $coupon = Coupon::factory()->create([
            'promo_type' => 'period',
            'is_active' => true,
            'end_date' => now()->subMinute(),
        ]);

        (new DeactivateCouponIfExpired($coupon->id))->handle();

        $coupon->refresh();
        $this->assertFalse($coupon->is_active);
    }

    #[Test]
    public function does_not_deactivate_period_coupon_before_end_date(): void
    {
        $coupon = Coupon::factory()->create([
            'promo_type' => 'period',
            'is_active' => true,
            'end_date' => now()->addHour(),
        ]);

        (new DeactivateCouponIfExpired($coupon->id))->handle();

        $coupon->refresh();
        $this->assertTrue($coupon->is_active);
    }

    #[Test]
    public function deactivates_product_coupon_after_end_date(): void
    {
        $coupon = Coupon::factory()->create([
            'promo_type' => 'product',
            'is_active' => true,
            'end_date' => now()->subMinute(),
        ]);

        (new DeactivateCouponIfExpired($coupon->id))->handle();

        $coupon->refresh();
        $this->assertFalse($coupon->is_active);
    }

    #[Test]
    public function does_not_deactivate_product_coupon_before_end_date(): void
    {
        $coupon = Coupon::factory()->create([
            'promo_type' => 'product',
            'is_active' => true,
            'end_date' => now()->addHour(),
        ]);

        (new DeactivateCouponIfExpired($coupon->id))->handle();

        $coupon->refresh();
        $this->assertTrue($coupon->is_active);
    }

    #[Test]
    public function deactivates_limit_coupon_when_usages_meet_or_exceed_limit(): void
    {
        $user = User::factory()->create();

        $coupon = Coupon::factory()->create([
            'promo_type' => 'limit',
            'is_active' => true,
            'limit' => 3,
        ]);

        // Create 3 usages
        CouponUsage::factory()->create(['coupon_id' => $coupon->id, 'user_id' => $user->id]);
        CouponUsage::factory()->create(['coupon_id' => $coupon->id, 'user_id' => $user->id]);
        CouponUsage::factory()->create(['coupon_id' => $coupon->id, 'user_id' => $user->id]);

        (new DeactivateCouponIfLimitReached($coupon->id))->handle();

        $coupon->refresh();
        $this->assertFalse($coupon->is_active);
    }

    #[Test]
    public function does_not_deactivate_limit_coupon_when_usages_below_limit(): void
    {
        $user = User::factory()->create();

        $coupon = Coupon::factory()->create([
            'promo_type' => 'limit',
            'is_active' => true,
            'limit' => 3,
        ]);

        // Create 2 usages (below the limit)
        CouponUsage::factory()->create(['coupon_id' => $coupon->id, 'user_id' => $user->id]);
        CouponUsage::factory()->create(['coupon_id' => $coupon->id, 'user_id' => $user->id]);

        (new DeactivateCouponIfLimitReached($coupon->id))->handle();

        $coupon->refresh();
        $this->assertTrue($coupon->is_active);
    }
}
