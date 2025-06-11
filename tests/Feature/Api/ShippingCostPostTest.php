<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Api\ShippingCostRequest;
use App\Models\Shipping;
use Database\Seeders\{CitySeeder, ProvinceSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
                'exists:cities,id',
            ],
            'weight' => [
                'required',
                'integer',
                'max:' . Shipping::MAX_WEIGHT
            ],
        ], (new ShippingCostRequest())->rules());
    }

    #[Test]
    public function can_get_shipping_costs()
    {
        $this->seed([ProvinceSeeder::class, CitySeeder::class]);

        $response = $this->postJson('/api/shipping_costs', ['destination' => 154, 'weight' => 1500]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'courier',
                        'courier_name',
                        'service',
                        'service_name',
                        'cost',
                        'etd',
                    ]
                ]
            ])
            ->assertJsonFragment([
                'courier' => 'jne',
                'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
                'service' => 'REG',
                'service_name' => 'Layanan Reguler',
                'cost' => 34000,
                'etd' => '1-2 hari'
            ])
            ->dump('data');
    }
}
