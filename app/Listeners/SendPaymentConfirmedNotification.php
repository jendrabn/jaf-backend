<?php

namespace App\Listeners;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Events\PaymentConfirmed;
use App\Models\UserNotification;

class SendPaymentConfirmedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmed $event): void
    {
        $order = $event->order;
        $title = 'Pembayaran kamu sudah kami terima';

        // Check if notification already exists
        if (UserNotification::existsForOrder($order->user_id, $order->id, $title)) {
            return;
        }

        UserNotification::create([
            'user_id' => $order->user_id,
            'title' => $title,
            'body' => "Pembayaran untuk pesanan #{$order->id} telah berhasil dikonfirmasi. Pesanan kamu sedang diproses.",
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::SUCCESS->value,
            'url' => "/account/orders/{$order->id}",
            'icon' => 'bi bi-check-circle-fill',
            'meta' => [
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'status' => $order->status,
            ],
        ]);
    }
}
