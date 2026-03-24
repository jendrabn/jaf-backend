<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class RegionCityGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_all_cities_by_province_id()
    {
        $this->fakeRajaOngkirApi(
            cities: [
                ['id' => 154, 'province_id' => 11, 'name' => 'Jakarta Timur'],
                ['id' => 155, 'province_id' => 11, 'name' => 'Jakarta Barat'],
            ],
        );

        $response = $this->getJson('/api/region/cities/11');

        $response->assertOk()
            ->assertJson([
                [
                    'id' => 154,
                    'province_id' => 11,
                    'name' => 'Jakarta Timur',
                ],
                [
                    'id' => 155,
                    'province_id' => 11,
                    'name' => 'Jakarta Barat',
                ],
            ]);
    }
}
