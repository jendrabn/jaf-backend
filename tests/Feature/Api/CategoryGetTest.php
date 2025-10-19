<?php

namespace Tests\Feature\Api;

use App\Models\ProductCategory;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class CategoryGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_all_categories(): void
    {
        $this->seed(ProductCategorySeeder::class);

        $categories = ProductCategory::all();

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJson(['data' => $this->formatCategoryData($categories)])
            ->assertJsonCount(3, 'data');
    }
}
