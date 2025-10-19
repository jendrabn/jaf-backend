<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\RajaOngkirService;
use Database\Seeders\CourierSeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderGatewayFeePostTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed required data so factories can create products and validation passes
        $this->seed([
            CourierSeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
        ]);

        $this->user = $this->createUser();

        // Configure a flat gateway fee of 4000 (percent = 0)
        config()->set('services.midtrans.fee_flat', 4000);
        config()->set('services.midtrans.fee_percent', 0);
        config()->set('services.midtrans.client_key', 'dummy-client-key');

        // Stub MidtransService to avoid external calls
        $this->app->instance(MidtransService::class, new class extends MidtransService
        {
            public function __construct()
            { /* skip parent initialize */
            }

            public function createTransaction(Order $order, Invoice $invoice, User $user): array
            {
                return [
                    'token' => 'dummy-token',
                    'redirect_url' => 'https://example.test/redirect',
                ];
            }
        });

        // Stub RajaOngkirService to return deterministic shipping costs
        $this->app->instance(RajaOngkirService::class, new class extends RajaOngkirService
        {
            public function __construct()
            { /* skip parent initialize */
            }

            public function calculateCost(int $destination, int $weight, ?string $courier = null): array
            {
                return [
                    [
                        'courier' => 'jne',
                        'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
                        'service' => 'REG',
                        'service_name' => 'Layanan Reguler',
                        'cost' => 34000,
                        'etd' => '1-2 hari',
                    ],
                ];
            }
        });
    }

    private function createCart(int $quantity = 1, ?array $productData = []): Cart
    {
        return Cart::factory()
            ->for(Product::factory()->create($productData))
            ->for($this->user)
            ->create(['quantity' => $quantity]);
    }

    private function attemptToCreateOrder(array $payload): TestResponse
    {
        Sanctum::actingAs($this->user);

        return $this->postJson('/api/orders', $payload);
    }

    #[Test]
    public function create_order_uses_the_correct_form_request_for_gateway()
    {
        $this->assertActionUsesFormRequest(
            OrderController::class,
            'create',
            CreateOrderRequest::class
        );
    }

    #[Test]
    public function can_create_order_with_gateway_fee_persisted_and_included_in_totals()
    {
        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);

        $payload = [
            'cart_ids' => [$cart1->id, $cart2->id],
            'shipping_address' => [
                'name' => 'Garfield',
                'phone' => '+6282310009788',
                'province_id' => 31,
                'city_id' => 3172,
                'district_id' => 3172050,
                'subdistrict_id' => 3172050005,
                'zip_code' => '13845',
                'address' => 'Jl. Belimbing XII No.19',
            ],
            'shipping_courier' => 'jne',
            'shipping_service' => 'REG',
            'payment_method' => 'gateway',
            'note' => fake()->sentence(),
        ];

        $response = $this->attemptToCreateOrder($payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'total_amount',
                    'payment_method',
                    'payment_info' => ['provider', 'client_key', 'fee', 'snap_token', 'redirect_url'],
                    'payment_due_date',
                    'created_at',
                ],
            ]);

        $orderId = $response->json('data.id');
        $order = Order::with(['invoice', 'invoice.payment'])->findOrFail($orderId);

        // Base computation from persisted fields
        $base = max(0, ($order->total_price - max(0, $order->discount)) + max(0, $order->shipping_cost) + $order->tax_amount);

        // Assert gateway fee persisted
        $this->assertSame(4000, (int) $order->gateway_fee);
        $this->assertSame('Payment Gateway Fee', $order->gateway_fee_name);

        // Invoice amount includes gateway fee
        $this->assertSame($base + 4000, (int) $order->invoice->amount);

        // Payment method & info contains fee and snap payload
        $this->assertSame(Payment::METHOD_GATEWAY, $order->invoice->payment->method);
        $info = $order->invoice->payment->info;
        $this->assertSame('midtrans', $info['provider'] ?? null);
        $this->assertSame('dummy-client-key', $info['client_key'] ?? null);
        $this->assertSame(4000, (int) ($info['fee'] ?? 0));
        $this->assertSame('dummy-token', $info['snap_token'] ?? null);
        $this->assertSame('https://example.test/redirect', $info['redirect_url'] ?? null);

        // Database assertions
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'gateway_fee' => 4000,
            'gateway_fee_name' => 'Payment Gateway Fee',
        ]);

        $this->assertDatabaseHas('invoices', [
            'order_id' => $orderId,
            'amount' => $base + 4000,
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $order->invoice->id,
            'method' => Payment::METHOD_GATEWAY,
            'amount' => $base + 4000,
        ]);
    }

    #[Test]
    public function checkout_endpoint_includes_gateway_fee_in_payment_methods()
    {
        Sanctum::actingAs($this->user);

        $cart = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);

        $resp = $this->postJson('/api/checkout', [
            'cart_ids' => [$cart->id],
        ]);

        $resp->assertOk()
            ->assertJsonPath('data.payment_methods.gateway.provider', 'midtrans')
            ->assertJsonPath('data.payment_methods.gateway.client_key', 'dummy-client-key')
            ->assertJsonPath('data.payment_methods.gateway.fee', 4000);
    }
}
