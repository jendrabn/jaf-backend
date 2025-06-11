<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\ConfirmPaymentRequest;
use App\Models\{Invoice, Order, Payment, User};
use Database\Seeders\BankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\ApiTestCase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
            'account_number' => '1988162520'
        ];
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
    public function confirm_payment_request_has_the_correct_validation_rules()
    {
        $this->markTestSkipped();

        $this->assertValidationRules([
            'name' => [
                'required',
                'string',
                'min:1',
                'max:50',
            ],
            'account_name' => [
                'required',
                'string',
                'min:1',
                'max:50',
            ],
            'account_number' => [
                'required',
                'string',
                'min:1',
                'max:50',
            ]
        ], (new ConfirmPaymentRequest())->rules());
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
        $order = Order::factory([
            'created_at' => now()
        ])
            ->for($this->user)
            ->afterCreating(
                fn($order) => Invoice::factory(['due_date' => $order->created_at->addDays(1)])
                    ->has(Payment::factory())
                    ->for($order)
                    ->create()
            )
            ->create(['status' => Order::STATUS_PENDING_PAYMENT]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/' . $order->id . '/confirm_payment', $this->data);

        $response->assertCreated()
            ->assertJson(['data' => true]);

        $this->assertDatabaseHas('payment_banks', $this->data);
        $this->assertTrue($order->fresh()->status === Order::STATUS_PENDING);
    }

    #[Test]
    public function cannot_confirm_payment_if_order_doenot_exist()
    {
        $this->markTestSkipped();

        $order = Order::factory()->for($this->createUser())->create();

        Sanctum::actingAs($this->user);

        // Unauthorized order id
        $response1 = $this->postJson('/api/orders/' . $order->id . '/confirm_payment', $this->data);

        $response1->assertNotFound()
            ->assertJsonStructure(['message']);

        // Invalid order id
        $response2 = $this->postJson('/api/orders/' . $order->id + 1 . '/confirm_payment', $this->data);

        $response2->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function cannot_confirm_payment_if_order_status_is_not_pending_payment()
    {
        $this->markTestSkipped();

        $order = Order::factory()
            ->for($this->user)
            ->afterCreating(
                fn($order) => Invoice::factory(['due_date' => $order->created_at->addDays(1)])
                    ->for($order)
                    ->create()
            )
            ->create(['status' => Order::STATUS_PENDING]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/' . $order->id . '/confirm_payment', $this->data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id']);
    }

    #[Test]
    public function cannot_confirm_payment_if_past_the_payment_due_date()
    {
        $this->markTestSkipped();

        $order = Order::factory()
            ->for($this->user)
            ->afterCreating(
                fn($order) => Invoice::factory(['due_date' => $order->created_at->addDays(1)])
                    ->for($order)
                    ->create()
            )
            ->create(['status' => Order::STATUS_PENDING_PAYMENT]);

        $this->travel(25)->hours();

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/' . $order->id . '/confirm_payment', $this->data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id']);

        $this->assertTrue($order->fresh()->status === Order::STATUS_CANCELLED);
    }
}
