<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class CancelOrderIfUnpaid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        try {
            DB::transaction(function (): void {
                $order = Order::query()
                    ->with('invoice')
                    ->lockForUpdate()
                    ->find($this->orderId);

                if (! $order) {
                    return;
                }

                if (now()->lt($order->created_at->copy()->addDay())) {
                    return;
                }

                if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
                    return;
                }

                if ($order->invoice && $order->invoice->status !== Invoice::STATUS_UNPAID) {
                    return;
                }

                $order->update([
                    'status' => Order::STATUS_CANCELLED,
                    'cancel_reason' => 'Order cancelled by system (24h timeout).',
                ]);
            });
        } catch (Throwable $e) {
            report($e);
            $this->release(60);
        }
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHour();
    }
}
