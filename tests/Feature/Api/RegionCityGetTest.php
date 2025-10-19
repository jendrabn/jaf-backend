<?php

namespace Tests\Feature\Api;

use App\Models\City;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class RegionCityGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_cities_by_province_id()
    {
        $this->seed([ProvinceSeeder::class, CitySeeder::class]);

        // DKI Jakarta Province
        $cities = City::where('province_id', 6)->get();

        $response = $this->getJson('/api/region/cities/6');

        $response->assertOk()
            ->assertJson(['data' => $this->formatCityData($cities)])
            ->assertJsonCount(6, 'data');
    }

    #[Test]
    public function returns_not_found_error_if_province_id_doenot_exist()
    {
        $this->seed([ProvinceSeeder::class, CitySeeder::class]);

        $response = $this->getJson('/api/region/cities/35');

        $response->assertNotFound()
            ->assertJsonStructure(['message']);
    }
}
