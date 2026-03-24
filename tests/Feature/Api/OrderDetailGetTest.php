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
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
        ]);

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
        $product = Product::factory()->hasImages()->create();

        $order = Order::factory()
            ->has(OrderItem::factory()->for($product), 'items')
            ->has(Invoice::factory()->has(Payment::factory()))
            ->has(Shipping::factory())
            ->for($this->user)
            ->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders/'.$order->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.items.0.id', $order->items->first()->id)
            ->assertJsonPath('data.items.0.product.id', $product->id)
            ->assertJsonPath('data.invoice.id', $order->invoice->id)
            ->assertJsonPath('data.invoice.number', $order->invoice->number)
            ->assertJsonPath('data.payment.id', $order->invoice->payment->id)
            ->assertJsonPath('data.payment.method', $order->invoice->payment->method)
            ->assertJsonPath('data.shipping_address.name', $order->shipping->address['name'])
            ->assertJsonPath('data.shipping_address.zip_code', $order->shipping->address['zip_code'])
            ->assertJsonPath('data.shipping.id', $order->shipping->id)
            ->assertJsonPath('data.shipping.service', $order->shipping->service)
            ->assertJsonPath('data.shipping.status', $order->shipping->status)
            ->assertJsonPath('data.status', $order->status)
            ->assertJsonPath('data.total_weight', $order->shipping->weight)
            ->assertJsonPath('data.total_price', $order->total_price)
            ->assertJsonPath('data.shipping_cost', $order->shipping_cost)
            ->assertJsonPath('data.total_amount', $order->invoice->amount);

        $this->assertStringStartsWith('http', $response['data']['items'][0]['product']['image']);
    }

    #[Test]
    public function returns_not_found_error_if_order_doenot_exist()
    {
        $otherUserOrder = Order::factory()
            ->has(OrderItem::factory()->for($this->createProduct()), 'items')
            ->has(Invoice::factory()->has(Payment::factory()))
            ->has(Shipping::factory())
            ->for($this->createUser())
            ->create();

        Sanctum::actingAs($this->user);

        $unauthorizedOrderResponse = $this->getJson('/api/orders/'.$otherUserOrder->id);
        $invalidOrderResponse = $this->getJson('/api/orders/'.($otherUserOrder->id + 1));

        $unauthorizedOrderResponse->assertNotFound()
            ->assertJsonStructure(['message']);

        $invalidOrderResponse->assertNotFound()
            ->assertJsonStructure(['message']);
    }
}
