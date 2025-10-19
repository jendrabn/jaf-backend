<?php

namespace Tests\Feature\Jobs;

use App\Jobs\CancelOrderIfUnpaid;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CancelOrderIfUnpaidTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cancels_order_after_24_hours_if_unpaid(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'created_at' => now()->subDay()->subMinute(), // >24h ago
        ]);

        Invoice::factory()->create([
            'order_id' => $order->id,
            'amount' => 10000,
            'status' => Invoice::STATUS_UNPAID,
            'due_date' => $order->created_at->copy()->addDay(),
        ]);

        (new CancelOrderIfUnpaid($order->id))->handle();

        $order->refresh();

        $this->assertSame(Order::STATUS_CANCELLED, $order->status);
        $this->assertNotNull($order->cancel_reason);
    }

    #[Test]
    public function does_not_cancel_if_not_timeout_or_paid_or_status_not_pending_payment(): void
    {
        $user = User::factory()->create();

        // Not yet 24h timeout
        $order1 = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'created_at' => now()->subHours(6),
        ]);

        Invoice::factory()->create([
            'order_id' => $order1->id,
            'amount' => 10000,
            'status' => Invoice::STATUS_UNPAID,
            'due_date' => $order1->created_at->copy()->addDay(),
        ]);

        (new CancelOrderIfUnpaid($order1->id))->handle();
        $order1->refresh();
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order1->status);

        // Already paid
        $order2 = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'created_at' => now()->subDay()->subMinute(),
        ]);

        Invoice::factory()->create([
            'order_id' => $order2->id,
            'amount' => 10000,
            'status' => Invoice::STATUS_PAID,
            'due_date' => $order2->created_at->copy()->addDay(),
        ]);

        (new CancelOrderIfUnpaid($order2->id))->handle();
        $order2->refresh();
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order2->status);

        // Status not pending payment
        $order3 = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING,
            'created_at' => now()->subDay()->subMinute(),
        ]);

        Invoice::factory()->create([
            'order_id' => $order3->id,
            'amount' => 10000,
            'status' => Invoice::STATUS_UNPAID,
            'due_date' => $order3->created_at->copy()->addDay(),
        ]);

        (new CancelOrderIfUnpaid($order3->id))->handle();
        $order3->refresh();
        $this->assertSame(Order::STATUS_PENDING, $order3->status);
    }
}
