<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Database\Seeders\BankSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderDetailGetTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    #[Test]
    public function unauthenticated_user_cannot_get_order_detail()
    {
        $response = $this->getJson('/api/orders/1');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_get_order_detail_by_order_id()
    {
        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            BankSeeder::class,
        ]);

        $order = Order::factory()
            ->has(OrderItem::factory()->for(Product::factory()->hasImages()->create()), 'items')
            ->has(Invoice::factory()->has(Payment::factory()))
            ->has(Shipping::factory())
            ->for($this->user)
            ->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders/'.$order->id);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'product' => $this->formatProductData($item->product),
                        'name' => $item->name,
                        'price' => $item->price,
                        'weight' => $item->weight,
                        'quantity' => $item->quantity,
                    ])->toArray(),
                    'invoice' => [
                        'id' => $order->invoice->id,
                        'number' => $order->invoice->number,
                        'amount' => $order->invoice->amount,
                        'due_date' => Carbon::parse($order->invoice->due_date)->toISOString(),
                        'status' => $order->invoice->status,
                    ],
                    'payment' => [
                        'id' => $order->invoice->payment->id,
                        'method' => $order->invoice->payment->method,
                        'info' => [
                            'name' => $order->invoice->payment->info['name'],
                            'code' => $order->invoice->payment->info['code'],
                            'account_name' => $order->invoice->payment->info['account_name'],
                            'account_number' => $order->invoice->payment->info['account_number'],
                        ],
                        'amount' => $order->invoice->payment->amount,
                        'status' => $order->invoice->payment->status,
                    ],
                    'shipping_address' => [
                        'name' => $order->shipping->address['name'],
                        'phone' => $order->shipping->address['phone'],
                        'province' => $order->shipping->address['province'],
                        'city' => $order->shipping->address['city'],
                        'district' => $order->shipping->address['district'],
                        'postal_code' => $order->shipping->address['postal_code'],
                        'address' => $order->shipping->address['address'],
                    ],
                    'shipping' => [
                        'id' => $order->shipping->id,
                        'courir' => $order->shipping->courir,
                        'courier_name' => $order->shipping->courier_name,
                        'service' => $order->shipping->service,
                        'service_name' => $order->shipping->service_name,
                        'etd' => $order->shipping->etd,
                        'tracking_number' => $order->shipping->tracking_number,
                        'status' => $order->shipping->status,
                    ],
                    'note' => $order->note,
                    'cancel_reason' => $order->cancel_reason,
                    'status' => $order->status,
                    'total_quantity' => $order->items->reduce(fn ($carry, $item) => $carry + $item->quantity),
                    'total_weight' => $order->shipping->weight,
                    'total_price' => $order->total_price,
                    'shipping_cost' => $order->shipping_cost,
                    'total_amount' => $order->invoice->amount,
                    'payment_due_date' => Carbon::parse($order->invoice->due_date)->toISOString(),
                    'confirmed_at' => Carbon::parse($order->confirmed_at)->toISOString(),
                    'completed_at' => Carbon::parse($order->completed_at)->toISOString(),
                    'cancelled_at' => Carbon::parse($order->cancelled_at)->toISOString(),
                    'created_at' => Carbon::parse($order->created_at)->toISOString(),
                ],
            ]);

        $this->assertStringStartsWith('http', $response['data']['items'][0]['product']['image']);
    }

    #[Test]
    public function returns_not_found_error_if_order_doenot_exist()
    {
        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            BankSeeder::class,
        ]);

        $order = Order::factory()
            ->has(OrderItem::factory()->for($this->createProduct()), 'items')
            ->has(Invoice::factory()->has(Payment::factory()))
            ->has(Shipping::factory())
            ->for($this->createUser())
            ->create();

        Sanctum::actingAs($this->user);

        // Unauthorized order id
        $response1 = $this->getJson('/api/orders/'.$order->id);

        $response1->assertNotFound()
            ->assertJsonStructure(['message']);

        // Invalid order id
        $response2 = $this->getJson('/api/orders/'.$order->id + 1);

        $response2->assertNotFound()
            ->assertJsonStructure(['message']);
    }
}
