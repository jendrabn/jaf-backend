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

class DeactivateCouponIfLimitReached implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(public int $couponId) {}

    public function handle(): void
    {
        /** @var Model|Coupon|null $coupon */
        $coupon = Coupon::query()
            ->withCount('usages')
            ->find($this->couponId);

        if (! $coupon) {
            return;
        }

        if ($coupon->promo_type !== 'limit') {
            return;
        }

        if (! $coupon->is_active) {
            return;
        }

        if (is_null($coupon->limit)) {
            return;
        }

        if ($coupon->usages_count >= $coupon->limit) {
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
        report($e);
    }
}
