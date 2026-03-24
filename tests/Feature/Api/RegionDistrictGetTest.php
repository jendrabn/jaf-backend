<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class RegionDistrictGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_all_districts_by_city_id()
    {
        $this->fakeRajaOngkirApi(
            districts: [
                ['id' => 2550, 'city_id' => 154, 'name' => 'Cipayung'],
                ['id' => 2551, 'city_id' => 154, 'name' => 'Ciracas'],
            ],
        );

        $response = $this->getJson('/api/region/districts/154');

        $response->assertOk()
            ->assertJson([
                [
                    'id' => 2550,
                    'city_id' => 154,
                    'name' => 'Cipayung',
                ],
                [
                    'id' => 2551,
                    'city_id' => 154,
                    'name' => 'Ciracas',
                ],
            ]);
    }
}
