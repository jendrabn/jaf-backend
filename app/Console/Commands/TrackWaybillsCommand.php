<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Shipping;
use App\Services\RajaOngkirService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class TrackWaybillsCommand extends Command
{
    protected $signature = 'orders:track-waybills {--limit=50 : Max number of orders to check per run}';

    protected $description = 'Poll RajaOngkir for orders on delivery; mark delivered orders as completed and shipped.';

    public function __construct(public RajaOngkirService $rajaOngkir)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?? 50);
        if ($limit <= 0) {
            $limit = 50;
        }

        $this->info("orders:track-waybills - checking up to {$limit} orders...");

        $orders = Order::query()
            ->with('shipping')
            ->where('status', Order::STATUS_ON_DELIVERY)
            ->whereHas('shipping', function ($q) {
                $q->whereNotNull('tracking_number')
                    ->whereNotNull('courier');
            })
            ->limit($limit)
            ->get();

        $processed = 0;
        $updatedDelivered = 0;

        foreach ($orders as $order) {
            $processed++;
            $shipping = $order->shipping;

            try {
                $rawPhone = $shipping->address['phone'] ?? null;
                $lastPhone = null;
                if (is_string($rawPhone) && $rawPhone !== '') {
                    $digits = preg_replace('/\D+/', '', $rawPhone) ?? '';
                    if (strlen($digits) >= 5) {
                        $lastPhone = substr($digits, -5);
                    }
                }

                $data = $this->rajaOngkir->trackWaybill(
                    (string) $shipping->courier,
                    (string) $shipping->tracking_number,
                    $lastPhone
                );

                if ($data === null) {
                    $this->line("Order #{$order->id}: no tracking data returned");

                    continue;
                }

                $statusStr = strtoupper((string) ($data['delivery_status']['status'] ?? $data['summary']['status'] ?? ''));
                $delivered = (bool) ($data['delivered'] ?? false) || ($statusStr === 'DELIVERED');

                if ($delivered) {
                    DB::transaction(function () use ($order) {
                        $order->status = Order::STATUS_COMPLETED;
                        $order->completed_at = now();
                        $order->save();

                        $order->shipping()->update([
                            'status' => Shipping::STATUS_SHIPPED,
                        ]);
                    });

                    $updatedDelivered++;
                    $this->info("Order #{$order->id}: marked as DELIVERED (completed/shipped).");
                } else {
                    $this->line("Order #{$order->id}: current tracking status = {$statusStr}");
                }
            } catch (Throwable $e) {
                logger()->error('orders:track-waybills failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Order #{$order->id}: error - {$e->getMessage()}");
            }
        }

        $this->info("Done. Updated delivered: {$updatedDelivered} / processed: {$processed}");

        return Command::SUCCESS;
    }
}
