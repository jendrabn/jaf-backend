<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class RegionProvinceGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_all_provinces()
    {
        $this->fakeRajaOngkirApi(
            provinces: [
                ['id' => 11, 'name' => 'DKI Jakarta'],
                ['id' => 12, 'name' => 'Jawa Barat'],
            ],
        );

        $response = $this->getJson('/api/region/provinces');

        $response->assertOk()
            ->assertJson([
                [
                    'id' => 11,
                    'name' => 'DKI Jakarta',
                ],
                [
                    'id' => 12,
                    'name' => 'Jawa Barat',
                ],
            ]);
    }
}
