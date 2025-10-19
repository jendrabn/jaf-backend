<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderGetTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    public function attemptToGetOrderAndExpectOk(array $params = [])
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders?'.http_build_query($params));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'items',
                        'status',
                        'total_amount',
                        'payment_due_date',
                        'created_at',
                    ],
                ],
                'page' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                ],
            ]);

        return $response;
    }

    #[Test]
    public function unauthenticated_user_cannot_get_all_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_get_all_orders()
    {
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

        $order = Order::factory()
            ->has(OrderItem::factory()->for($this->createProduct()), 'items')
            ->has(Invoice::factory())
            ->for($this->user)
            ->create();

        $response = $this->attemptToGetOrderAndExpectOk();

        $response->assertJsonPath('data.0', [
            'id' => $order->id,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => $this->formatProductData($item->product),
                'name' => $item->name,
                'price' => $item->price,
                'weight' => $item->weight,
                'quantity' => $item->quantity,
            ])->toArray(),
            'status' => $order->status,
            'total_amount' => $order->invoice->amount,
            'payment_due_date' => $order->invoice->due_date->toISOString(),
            'created_at' => $order->created_at->toISOString(),
        ]);
    }

    #[Test]
    public function can_get_all_orders_with_pagination()
    {
        Order::factory($total = 13)
            ->for($this->user)
            ->has(Invoice::factory())
            ->create();

        $response = $this->attemptToGetOrderAndExpectOk(['page' => $page = 2]);

        $response->assertJsonPath('page', [
            'total' => $total,
            'per_page' => 10,
            'current_page' => $page,
            'last_page' => 2,
            'from' => 11,
            'to' => 13,
        ])->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_get_orders_by_status()
    {
        $statuses = [
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ];
        $orders = [];

        foreach ($statuses as $status) {
            $orders[$status] = Order::factory(3)
                ->for($this->user)
                ->has(Invoice::factory())
                ->create(['status' => $status]);
        }

        foreach ($statuses as $status) {
            $response = $this->attemptToGetOrderAndExpectOk(['status' => $status]);

            $response->assertJsonCount(3, 'data');

            $this->assertEquals(
                Arr::pluck($orders[$status]->sortByDesc('id'), 'id'),
                Arr::pluck($response['data'], 'id')
            );
        }
    }

    #[Test]
    public function can_sort_order_by_newest()
    {
        $orders = Order::factory(3)
            ->for($this->user)
            ->has(Invoice::factory())
            ->create();

        $response = $this->attemptToGetOrderAndExpectOk(['sort_by' => 'newest']);

        $response->assertJsonCount(3, 'data');

        $this->assertEquals(
            Arr::pluck($orders->sortByDesc('id'), 'id'),
            Arr::pluck($response['data'], 'id')
        );
    }

    #[Test]
    public function can_sort_order_by_oldest()
    {
        $orders = Order::factory(3)
            ->for($this->user)
            ->has(Invoice::factory())
            ->create();

        $response = $this->attemptToGetOrderAndExpectOk(['sort_by' => 'oldest']);

        $response->assertJsonCount(3, 'data');

        $this->assertEquals(
            Arr::pluck($orders->sortBy('id'), 'id'),
            Arr::pluck($response['data'], 'id')
        );
    }
}
