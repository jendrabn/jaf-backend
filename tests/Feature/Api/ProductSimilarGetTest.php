<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class ProductSimilarGetTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
    }

    #[Test]
    public function can_get_similar_products_by_product_slug()
    {
        $this->createProduct(count: 3);
        $products = Product::factory(7)
            ->sequence(fn ($sequence) => ['name' => 'Bvlgari Variant '.$sequence->index])
            ->create();
        $product = $products->first();

        $response = $this->getJson('/api/products/'.$product->slug.'/similars');

        $expectedProducts = $products->where('id', '!==', $product->id)->sortByDesc('id')->take(5);

        $response->assertJsonCount(5, 'data');

        $this->assertSame(
            $expectedProducts->pluck('id')->values()->toArray(),
            collect($response['data'])->pluck('id')->values()->toArray()
        );
    }

    #[Test]
    public function returns_not_found_error_if_product_slug_doenot_exist()
    {
        $product = $this->createProduct();

        $response = $this->getJson('/api/products/'.$product->slug.'-missing/similars');

        $response->assertNotFound()
            ->assertJsonStructure(['message']);
    }
}
