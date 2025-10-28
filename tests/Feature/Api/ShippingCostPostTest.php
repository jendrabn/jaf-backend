<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Api\ShippingCostRequest;
use App\Models\Shipping;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
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
                'exists:cities,id',
            ],
            'weight' => [
                'required',
                'integer',
                'max:' . Shipping::MAX_WEIGHT,
            ],
        ], (new ShippingCostRequest)->rules());
    }
}
