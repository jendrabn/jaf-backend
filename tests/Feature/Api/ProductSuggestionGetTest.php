<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class ProductSuggestionGetTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
    }

    #[Test]
    public function can_get_product_suggestions_by_query(): void
    {
        // Matching samples for "live" prefix
        Product::factory()->create(['name' => 'Liverpool Jersey']);
        Product::factory()->create(['name' => 'Liver Cleanse Supplement']);
        Product::factory()->create(['name' => 'Live Connection Cable']);

        // Some non-matching noise
        Product::factory()->create(['name' => 'Paris Perfume']);
        Product::factory()->create(['name' => 'Cable Organizer']);

        $response = $this->getJson('/api/products/suggestions?q=live');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);

        $data = $response->json('data');

        $this->assertIsArray($data);
        // Ensure token and phrase suggestions exist
        $this->assertContains('Liverpool', $data);
        $this->assertContains('Liver', $data);
        $this->assertContains('Live Connection', $data);
    }

    #[Test]
    public function validates_query_parameter(): void
    {
        $response = $this->getJson('/api/products/suggestions');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['q'],
            ]);
    }

    #[Test]
    public function can_limit_number_of_suggestions(): void
    {
        Product::factory()->create(['name' => 'Liverpool Jersey']);
        Product::factory()->create(['name' => 'Liver Cleanse Supplement']);
        Product::factory()->create(['name' => 'Live Connection Cable']);

        $response = $this->getJson('/api/products/suggestions?q=live&size=2');

        $response->assertOk();

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertLessThanOrEqual(2, count($data));
    }
}
