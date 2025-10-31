<?php

namespace App\Listeners;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Events\OrderCompleted;
use App\Models\UserNotification;

class SendOrderCompletedNotification
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
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;
        $title = 'Pesanan kamu sudah diterima 🎉';

        // Check if notification already exists
        if (UserNotification::existsForOrder($order->user_id, $order->id, $title)) {
            return;
        }

        UserNotification::create([
            'user_id' => $order->user_id,
            'title' => $title,
            'body' => "Terima kasih! Pesanan #{$order->id} telah selesai. Jangan lupa beri rating untuk produk yang kamu beli.",
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
