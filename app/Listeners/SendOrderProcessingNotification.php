<?php

namespace App\Listeners;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Events\OrderProcessing;
use App\Models\UserNotification;

class SendOrderProcessingNotification
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
    public function handle(OrderProcessing $event): void
    {
        $order = $event->order;
        $title = 'Pesanan kamu sedang dikemas 📦';

        // Check if notification already exists
        if (UserNotification::existsForOrder($order->user_id, $order->id, $title)) {
            return;
        }

        UserNotification::create([
            'user_id' => $order->user_id,
            'title' => $title,
            'body' => "Pesanan #{$order->id} sedang dipersiapkan oleh tim kami. Kami akan menginformasikan ketika pesanan sudah dikirim.",
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::INFO->value,
            'url' => "/account/orders/{$order->id}",
            'icon' => 'bi bi-box',
            'meta' => [
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'status' => $order->status,
            ],
        ]);
    }
}
