<?php

namespace App\Listeners;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Events\OrderCreated;
use App\Models\UserNotification;

class SendOrderCreatedNotification
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
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $title = 'Pesanan kamu berhasil dibuat';

        // Check if notification already exists
        if (UserNotification::existsForOrder($order->user_id, $order->id, $title)) {
            return;
        }

        UserNotification::create([
            'user_id' => $order->user_id,
            'title' => $title,
            'body' => "Pesanan #{$order->id} telah berhasil dibuat. Silakan lakukan pembayaran untuk melanjutkan proses.",
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::INFO->value,
            'url' => "/account/orders/{$order->id}",
            'icon' => 'bi bi-cart',
            'meta' => [
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'status' => $order->status,
            ],
        ]);
    }
}
