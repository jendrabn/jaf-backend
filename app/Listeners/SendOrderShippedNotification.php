<?php

namespace App\Listeners;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Events\OrderShipped;
use App\Models\UserNotification;

class SendOrderShippedNotification
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
    public function handle(OrderShipped $event): void
    {
        $order = $event->order;
        $shipping = $order->shipping;
        $title = 'Pesanan kamu sedang dikirim 🚚';

        // Check if notification already exists
        if (UserNotification::existsForOrder($order->user_id, $order->id, $title)) {
            return;
        }

        $trackingNumber = $shipping->tracking_number ?? 'Sedang disiapkan';
        $courierName = $shipping->courier_name ?? 'Kurir';

        UserNotification::create([
            'user_id' => $order->user_id,
            'title' => $title,
            'body' => "Pesanan #{$order->id} telah dikirim menggunakan {$courierName}. Nomor resi: {$trackingNumber}",
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::INFO->value,
            'url' => "/account/orders/{$order->id}",
            'icon' => 'bi bi-truck',
            'meta' => [
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'status' => $order->status,
                'tracking_number' => $trackingNumber,
                'courier' => $courierName,
            ],
        ]);
    }
}
