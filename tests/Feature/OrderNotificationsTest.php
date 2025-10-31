<?php

namespace Tests\Feature;

use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Events\OrderCancelled;
use App\Events\OrderCompleted;
use App\Events\OrderCreated;
use App\Events\OrderProcessing;
use App\Events\OrderShipped;
use App\Events\PaymentConfirmed;
use App\Listeners\SendOrderCancelledNotification;
use App\Listeners\SendOrderCompletedNotification;
use App\Listeners\SendOrderCreatedNotification;
use App\Listeners\SendOrderProcessingNotification;
use App\Listeners\SendOrderShippedNotification;
use App\Listeners\SendPaymentConfirmedNotification;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_creates_notification_when_order_is_created()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
        ]);

        $event = new OrderCreated($order);
        $listener = new SendOrderCreatedNotification;
        $listener->handle($event);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->user->id,
            'title' => 'Pesanan kamu berhasil dibuat 🧾',
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::INFO->value,
        ]);
    }

    #[Test]
    public function it_creates_notification_when_payment_is_confirmed()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $event = new PaymentConfirmed($order);
        $listener = new SendPaymentConfirmedNotification;
        $listener->handle($event);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->user->id,
            'title' => 'Pembayaran kamu sudah kami terima 💰',
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::SUCCESS->value,
        ]);
    }

    #[Test]
    public function it_creates_notification_when_order_is_processing()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        $event = new OrderProcessing($order);
        $listener = new SendOrderProcessingNotification;
        $listener->handle($event);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->user->id,
            'title' => 'Pesanan kamu sedang dikemas 📦',
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::INFO->value,
        ]);
    }

    #[Test]
    public function it_creates_notification_when_order_is_shipped()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'on_delivery',
        ]);

        // Create shipping with tracking number and address
        $order->shipping()->create([
            'tracking_number' => 'JNE123456789',
            'courier' => 'jne',
            'courier_name' => 'JNE',
            'service' => 'REG',
            'service_name' => 'Regular',
            'etd' => '2-3',
            'weight' => 1000,
            'status' => 'shipped',
            'address' => [
                'name' => 'Test User',
                'phone' => '08123456789',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Pusat',
                'district' => 'Menteng',
                'subdistrict' => 'Menteng',
                'zip_code' => '10310',
                'address' => 'Jl. Test No. 123',
            ],
        ]);

        $event = new OrderShipped($order);
        $listener = new SendOrderShippedNotification;
        $listener->handle($event);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->user->id,
            'title' => 'Pesanan kamu sedang dikirim 🚚',
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::INFO->value,
        ]);

        $notification = UserNotification::first();
        $this->assertStringContainsString('JNE123456789', $notification->body);
        $this->assertStringContainsString('JNE', $notification->body);
    }

    #[Test]
    public function it_creates_notification_when_order_is_completed()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $event = new OrderCompleted($order);
        $listener = new SendOrderCompletedNotification;
        $listener->handle($event);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->user->id,
            'title' => 'Pesanan kamu sudah diterima 🎉',
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::SUCCESS->value,
        ]);
    }

    #[Test]
    public function it_creates_notification_when_order_is_cancelled()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'cancelled',
            'cancel_reason' => 'Stok habis',
        ]);

        $event = new OrderCancelled($order);
        $listener = new SendOrderCancelledNotification;
        $listener->handle($event);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->user->id,
            'title' => 'Pesanan kamu dibatalkan ❌',
            'category' => NotificationCategory::TRANSACTION->value,
            'level' => NotificationLevel::ERROR->value,
        ]);

        $notification = UserNotification::first();
        $this->assertStringContainsString('Stok habis', $notification->body);
    }

    #[Test]
    public function notification_meta_contains_order_information()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'total_price' => 150000,
        ]);

        $event = new OrderCreated($order);
        $listener = new SendOrderCreatedNotification;
        $listener->handle($event);

        $notification = UserNotification::first();

        $this->assertArrayHasKey('order_id', $notification->meta);
        $this->assertArrayHasKey('total_price', $notification->meta);
        $this->assertArrayHasKey('status', $notification->meta);

        $this->assertEquals($order->id, $notification->meta['order_id']);
        $this->assertEquals(150000, $notification->meta['total_price']);
        $this->assertEquals('pending_payment', $notification->meta['status']);
    }
}
