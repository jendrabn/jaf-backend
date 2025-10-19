<?php

namespace App\Jobs;

use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class DeactivateCouponIfExpired implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $couponId) {}

    public function handle(): void
    {
        /** @var Model|Coupon|null $coupon */
        $coupon = Coupon::query()->find($this->couponId);

        if (! $coupon) {
            return;
        }

        // Only applicable to period/product promo types
        if (! in_array($coupon->promo_type, ['period', 'product'], true)) {
            return;
        }

        // No end_date defined, cannot expire by period
        if (! $coupon->end_date) {
            return;
        }

        // If already inactive, nothing to do
        if (! $coupon->is_active) {
            return;
        }

        // If now is after end_date => deactivate
        if (now()->greaterThanOrEqualTo($coupon->end_date)) {
            $coupon->update([
                'is_active' => false,
            ]);
        }
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(2);
    }

    public function failed(Throwable $e): void
    {
        // Optionally release to try later, but default failed handling is sufficient.
        report($e);
    }
}
