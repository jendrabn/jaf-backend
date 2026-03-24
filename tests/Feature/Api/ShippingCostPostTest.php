<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Api\ShippingCostRequest;
use App\Models\Shipping;
use Database\Seeders\CourierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class ShippingCostPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function shipping_costs_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(CheckoutController::class, 'shippingCosts', ShippingCostRequest::class);
    }

    #[Test]
    public function shipping_cost_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'destination' => [
                'required',
                'integer',
            ],
            'weight' => [
                'required',
                'integer',
                'max:'.Shipping::MAX_WEIGHT,
            ],
        ], (new ShippingCostRequest)->rules());
    }

    #[Test]
    public function can_get_shipping_costs()
    {
        $this->seed(CourierSeeder::class);
        $this->fakeRajaOngkirApi();

        $response = $this->postJson('/api/shipping_costs', [
            'destination' => 154,
            'weight' => 1500,
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'courier' => 'jne',
                        'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
                        'service' => 'REG',
                        'service_name' => 'Layanan Reguler',
                        'cost' => 34000,
                        'etd' => '1-2 hari',
                    ],
                ],
            ]);
    }
}
