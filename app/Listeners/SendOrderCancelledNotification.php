<?php

namespace App\Listeners;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Events\OrderCancelled;
use App\Models\UserNotification;

class SendOrderCancelledNotification
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
    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;
        $title = 'Pesanan kamu dibatalkan';

        // Check if notification already exists
        if (UserNotification::existsForOrder($order->user_id, $order->id, $title)) {
            return;
        }

        $cancelReason = $order->cancel_reason ?? 'Tidak ada alasan spesifik';

        UserNotification::create([
            'user_id' => $order->user_id,
            'title' => $title,
            'body' => "Pesanan #{$order->id} telah dibatalkan. Alasan: {$cancelReason}",
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::ERROR->value,
            'url' => "/account/orders/{$order->id}",
            'icon' => 'bi bi-x-circle-fill',
            'meta' => [
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'status' => $order->status,
                'cancel_reason' => $cancelReason,
            ],
        ]);
    }
}
