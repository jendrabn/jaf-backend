<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class ProductGetTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
    }

    private function attemptToGetProductAndExpectOk(array $params = [])
    {
        $response = $this->getJson('/api/products?'.http_build_query($params));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'image',
                        'category',
                        'brand',
                        'sex',
                        'price',
                        'stock',
                        'weight',
                        'sold_count',
                        'is_wishlist',
                    ],
                ],
                'page' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                ],
            ]);

        return $response;
    }

    #[Test]
    public function test_can_get_all_products()
    {
        $products = Product::factory(3)->hasImages()->create();

        $this->createProduct(['is_publish' => false]);

        $expectedProducts = $products->sortByDesc('id');

        $response = $this->attemptToGetProductAndExpectOk();

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(3, 'data');

        $this->assertStringStartsWith('http', $response['data'][0]['image']);
    }

    #[Test]
    public function test_can_get_products_by_page()
    {
        $this->createProduct(count: 23);

        $response = $this->attemptToGetProductAndExpectOk(['page' => 2]);

        $response->assertJsonPath('page', [
            'total' => 23,
            'per_page' => 20,
            'current_page' => 2,
            'last_page' => 2,
            'from' => 21,
            'to' => 23,
        ])->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_get_products_by_search()
    {
        $products = Product::factory(3)
            ->sequence(
                [
                    'name' => 'Parfum Aroma Bunga Mawar',
                    'product_category_id' => 1,
                    'product_brand_id' => 1,
                ],
                [
                    'name' => 'Parfum Aroma Jeruk',
                    'product_category_id' => $this->createCategory(['name' => '100ml'])->id,
                    'product_brand_id' => 2,
                ],
                [
                    'name' => 'Parfum Wangi Bunga Jasmine',
                    'product_category_id' => 2,
                    'product_brand_id' => $this->createBrand(['name' => 'Roses Musk'])->id,
                ],
            )->create();

        // Search by name name
        $response = $this->attemptToGetProductAndExpectOk(['search' => 'Bunga Mawar']);

        $response->assertJsonPath('data.0', $this->formatProductData($products[0]))
            ->assertJsonCount(1, 'data');

        // Search by category name
        $response = $this->attemptToGetProductAndExpectOk(['search' => '100ml']);

        $response->assertJsonPath('data.0', $this->formatProductData($products[1]))
            ->assertJsonCount(1, 'data');

        // Search by brand name
        $response = $this->attemptToGetProductAndExpectOk(['search' => 'Musk']);

        $response->assertJsonPath('data.0', $this->formatProductData($products[2]))
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_sort_products_by_newest()
    {
        $products = $this->createProduct(count: 3);

        $expectedProducts = $products->sortByDesc('id');

        $response = $this->attemptToGetProductAndExpectOk(['sort_by' => 'newest']);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_sort_products_by_oldest()
    {
        $products = $this->createProduct(count: 3);

        $expectedProducts = $products->sortBy('id');

        $response = $this->attemptToGetProductAndExpectOk(['sort_by' => 'oldest']);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_sort_products_by_sales()
    {
        $product1 = $this->createProductWithSales(quantities: [3, 2]);
        $product2 = $this->createProductWithSales(quantities: [2, 1]);
        $this->createProductWithSales(status: Order::STATUS_PENDING);
        $this->createProductWithSales(status: Order::STATUS_PROCESSING);
        $this->createProductWithSales(status: Order::STATUS_ON_DELIVERY);

        $response = $this->attemptToGetProductAndExpectOk(['sort_by' => 'sales']);

        $response
            ->assertJsonPath('data.0.id', $product1->id)
            ->assertJsonPath('data.0.sold_count', 5)
            ->assertJsonPath('data.1.id', $product2->id)
            ->assertJsonPath('data.1.sold_count', 3)
            ->assertJsonCount(5, 'data');

        $this->assertCount(
            3,
            Arr::where($response['data'], fn ($product) => $product['sold_count'] === 0)
        );
    }

    #[Test]
    public function can_sort_products_by_expensive()
    {
        $products = Product::factory(3)
            ->sequence(
                ['price' => 1000],
                ['price' => 3000],
                ['price' => 2000],
            )
            ->create();

        $expectedProducts = $products->sortByDesc('price');

        $response = $this->attemptToGetProductAndExpectOk(['sort_by' => 'expensive']);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_sort_products_by_cheapest()
    {
        $products = Product::factory(3)
            ->sequence(
                ['price' => 3000],
                ['price' => 1000],
                ['price' => 2000]
            )
            ->create();

        $expectedProducts = $products->sortBy('price');

        $response = $this->attemptToGetProductAndExpectOk(['sort_by' => 'cheapest']);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_filter_products_by_category_id()
    {
        $products = Product::factory(3)
            ->sequence(
                ['product_category_id' => 1],
                ['product_category_id' => 1],
                ['product_category_id' => 2],
            )
            ->create();

        $expectedProducts = $products->where('product_category_id', 1)->sortByDesc('id');

        $response = $this->attemptToGetProductAndExpectOk(['category_id' => 1]);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function can_filter_products_by_brand_id()
    {
        $products = Product::factory(3)
            ->sequence(
                ['product_brand_id' => 1],
                ['product_brand_id' => 1],
                ['product_brand_id' => 2],
            )
            ->create();

        $expectedProducts = $products->where('product_brand_id', 1)->sortByDesc('id');

        $response = $this->attemptToGetProductAndExpectOk(['brand_id' => 1]);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function can_filter_products_by_sex()
    {
        $products = Product::factory(3)
            ->sequence(
                ['sex' => 1],
                ['sex' => 1],
                ['sex' => 2],
            )
            ->create();

        $expectedProducts = $products->where('sex', 1)->sortByDesc('id');

        $response = $this->attemptToGetProductAndExpectOk(['sex' => 1]);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function can_filter_products_by_price_min_and_price_max()
    {
        $products = Product::factory(5)
            ->sequence(
                ['price' => 500],
                ['price' => 3000],
                ['price' => 1000],
                ['price' => 5000],
                ['price' => 7000],
            )
            ->create();

        $expectedProducts = $products->whereBetween('price', [$min = 1000, $max = 5000])->sortByDesc('id');

        $response = $this->attemptToGetProductAndExpectOk([
            'min_price' => $min,
            'max_price' => $max,
        ]);

        $response->assertJsonPath('data', $this->formatProductData($expectedProducts))
            ->assertJsonCount(3, 'data');
    }
}
