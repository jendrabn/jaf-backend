<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderWaybillGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_track_order_waybill()
    {
        $response = $this->getJson('/api/orders/1/waybill');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_track_order_waybill()
    {
        $user = $this->createUser();
        $order = Order::factory()->for($user)->create();

        Shipping::factory()->create([
            'order_id' => $order->id,
            'courier' => 'jne',
            'tracking_number' => 'RESI123456789',
            'address' => [
                'name' => 'Garfield',
                'phone' => '081234567890',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Timur',
                'district' => 'Cipayung',
                'zip_code' => '13845',
                'address' => 'Jl. Belimbing XII No.19',
            ],
        ]);

        Http::fake([
            '*track/waybill*' => Http::response([
                'data' => [
                    'summary' => [
                        'awb' => 'RESI123456789',
                        'courier' => 'jne',
                        'status' => 'DELIVERED',
                    ],
                    'details' => [
                        [
                            'date' => '2026-03-24 10:00:00',
                            'desc' => 'Package delivered',
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/orders/'.$order->id.'/waybill');

        $response->assertOk()
            ->assertJsonPath('meta.status', 'success')
            ->assertJsonPath('data.summary.awb', 'RESI123456789')
            ->assertJsonPath('data.summary.courier', 'jne');
    }

    #[Test]
    public function returns_error_meta_when_waybill_tracking_cannot_be_fetched()
    {
        $user = $this->createUser();
        $order = Order::factory()->for($user)->create();

        Shipping::factory()->create([
            'order_id' => $order->id,
            'courier' => 'jne',
            'tracking_number' => 'RESI123456789',
            'address' => [
                'name' => 'Garfield',
                'phone' => '081234567890',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Timur',
                'district' => 'Cipayung',
                'zip_code' => '13845',
                'address' => 'Jl. Belimbing XII No.19',
            ],
        ]);

        Http::fake([
            '*track/waybill*' => Http::response([], 500),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/orders/'.$order->id.'/waybill');

        $response->assertOk()
            ->assertJsonPath('meta.status', 'error')
            ->assertJsonPath('data', []);
    }
}
