<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\ConfirmPaymentRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\BankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderConfirmPaymentPostTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    private array $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BankSeeder::class);
        $this->user = $this->createUser();
        $this->data = [
            'name' => 'BCA',
            'account_name' => 'Abdullah',
            'account_number' => '1988162520',
        ];
    }

    private function createPendingPaymentOrder(User $user, array $orderAttributes = [], array $invoiceAttributes = []): Order
    {
        return Order::factory([
            'created_at' => now(),
        ])
            ->for($user)
            ->afterCreating(
                fn ($order) => Invoice::factory(array_merge([
                    'due_date' => $order->created_at->copy()->addDay(),
                ], $invoiceAttributes))
                    ->has(Payment::factory([
                        'method' => Payment::METHOD_BANK,
                    ]))
                    ->for($order)
                    ->create()
            )
            ->create(array_merge([
                'status' => Order::STATUS_PENDING_PAYMENT,
            ], $orderAttributes));
    }

    #[Test]
    public function confirm_payment_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            OrderController::class,
            'confirmPayment',
            ConfirmPaymentRequest::class
        );
    }

    #[Test]
    public function confirm_payment_request_requires_bank_transfer_fields()
    {
        $order = $this->createPendingPaymentOrder($this->user);
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/'.$order->id.'/confirm_payment', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'account_name', 'account_number']);
    }

    #[Test]
    public function unauthenticated_user_cannot_confirm_payment()
    {
        $response = $this->postJson('/api/orders/1/confirm_payment');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_confirm_payment()
    {
        $order = $this->createPendingPaymentOrder($this->user);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/'.$order->id.'/confirm_payment', $this->data);

        $response->assertCreated()
            ->assertJson(['data' => true]);

        $this->assertDatabaseHas('payment_banks', $this->data);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    #[Test]
    public function cannot_confirm_payment_if_order_doenot_exist()
    {
        $otherUserOrder = $this->createPendingPaymentOrder($this->createUser());

        Sanctum::actingAs($this->user);

        $unauthorizedOrderResponse = $this->postJson('/api/orders/'.$otherUserOrder->id.'/confirm_payment', $this->data);
        $invalidOrderResponse = $this->postJson('/api/orders/'.($otherUserOrder->id + 1).'/confirm_payment', $this->data);

        $unauthorizedOrderResponse->assertNotFound()
            ->assertJsonStructure(['message']);

        $invalidOrderResponse->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function cannot_confirm_payment_if_order_status_is_not_pending_payment()
    {
        $order = $this->createPendingPaymentOrder($this->user, [
            'status' => Order::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/'.$order->id.'/confirm_payment', $this->data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id']);
    }

    #[Test]
    public function cannot_confirm_payment_if_past_the_payment_due_date()
    {
        $order = $this->createPendingPaymentOrder($this->user, [], [
            'due_date' => now()->subMinute(),
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/'.$order->id.'/confirm_payment', $this->data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id']);

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
    }
}
