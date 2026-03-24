<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class RegionSubDistrictGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_all_subdistricts_by_district_id()
    {
        $this->fakeRajaOngkirApi(
            subdistricts: [
                ['id' => 3500, 'district_id' => 2550, 'name' => 'Cilangkap'],
                ['id' => 3501, 'district_id' => 2550, 'name' => 'Lubang Buaya'],
            ],
        );

        $response = $this->getJson('/api/region/sub-districts/2550');

        $response->assertOk()
            ->assertJson([
                [
                    'id' => 3500,
                    'district_id' => 2550,
                    'name' => 'Cilangkap',
                ],
                [
                    'id' => 3501,
                    'district_id' => 2550,
                    'name' => 'Lubang Buaya',
                ],
            ]);
    }
}
