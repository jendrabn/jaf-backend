<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelExpiredOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancel-expired-order';

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
        $orders = Order::where('status', Order::STATUS_PENDING_PAYMENT)
            ->whereHas('invoice', fn($q) => $q->where('due_date', '<', now()))
            ->get();

        if ($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                $order->update([
                    'status' => Order::STATUS_CANCELLED,
                    'cancel_reason' => 'Pesanan dibatalkan otomatis oleh sistem!'
                ]);
            }
        }
    }
}
