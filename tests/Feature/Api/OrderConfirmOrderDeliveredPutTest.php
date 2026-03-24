<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderConfirmOrderDeliveredPutTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    #[Test]
    public function unauthenticated_user_cannot_confirm_order_delivered()
    {
        $response = $this->putJson('/api/orders/1/confirm_order_delivered');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_confirm_order_delivered()
    {
        $order = Order::factory()
            ->for($this->user)
            ->create(['status' => Order::STATUS_ON_DELIVERY]);
        Shipping::factory()->create([
            'order_id' => $order->id,
            'status' => Shipping::STATUS_PROCESSING,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/orders/'.$order->id.'/confirm_order_delivered');

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertTrue($order->fresh()->status === Order::STATUS_COMPLETED);
        $this->assertTrue($order->shipping->fresh()->status === Shipping::STATUS_SHIPPED);
    }

    #[Test]
    public function cannot_confirm_order_delivered_if_order_doenot_exist()
    {
        $order = Order::factory()->for(User::factory())->create();

        Sanctum::actingAs($this->user);

        // Unauthorized order id
        $response1 = $this->putJson('/api/orders/'.$order->id.'/confirm_order_delivered');

        $response1->assertNotFound()
            ->assertJsonStructure(['message']);

        // Invalid order id
        $response2 = $this->putJson('/api/orders/'.$order->id + 1 .'/confirm_order_delivered');

        $response2->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function cannot_confirm_order_delivered_if_order_status_is_not_on_delivery()
    {
        $order = Order::factory()
            ->for($this->user)
            ->create(['status' => Order::STATUS_PROCESSING]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/orders/'.$order->id.'/confirm_order_delivered');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id']);
    }
}
