<?php

namespace Tests\Feature\Api;

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSaleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_running_flash_sales_with_products(): void
    {
        $category = ProductCategory::factory()->create();
        $brand = ProductBrand::factory()->create();

        $flashSale = FlashSale::factory()->running()->create();

        $products = Product::factory()
            ->count(2)
            ->for($category, 'category')
            ->for($brand, 'brand')
            ->create();

        $flashSale->products()->attach(
            $products->mapWithKeys(function (Product $product, int $index) {
                return [
                    $product->id => [
                        'flash_price' => 50000 - ($index * 1000),
                        'stock_flash' => 20 + $index,
                        'sold' => $index,
                        'max_qty_per_user' => 2,
                    ],
                ];
            })->all()
        );

        $response = $this->getJson('/api/flash-sale');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.products')
            ->assertJsonPath('data.0.status', 'running')
            ->assertJsonPath('data.0.products.0.flash_price', 50000)
            ->assertJsonPath('data.0.products.0.flash_stock_remaining', 20)
            ->assertJsonPath('data.0.products.1.flash_stock_remaining', 20)
            ->assertJsonPath('data.0.products.1.max_qty_per_user', 2);

        $productResponse = $this->getJson('/api/products/'.$products->first()->slug);

        $productResponse->assertOk()
            ->assertJsonPath('data.flash_sale_price', 50000)
            ->assertJsonPath('data.is_in_flash_sale', true)
            ->assertJsonPath('data.flash_sale_end_at', $flashSale->end_at->format('Y-m-d H:i:s'));
    }

    public function test_flash_sale_is_returned_when_schedule_matches(): void
    {
        $category = ProductCategory::factory()->create();
        $brand = ProductBrand::factory()->create();

        $flashSale = FlashSale::factory()->create([
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addHour(),
            'is_active' => true,
        ]);

        $product = Product::factory()
            ->for($category, 'category')
            ->for($brand, 'brand')
            ->create();

        $flashSale->products()->attach($product->id, [
            'flash_price' => 45000,
            'stock_flash' => 15,
            'sold' => 1,
            'max_qty_per_user' => 2,
        ]);

        $response = $this->getJson('/api/flash-sale');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'running')
            ->assertJsonPath('data.0.products.0.flash_price', 45000);
    }

    public function test_it_ignores_inactive_or_non_running_flash_sales(): void
    {
        $category = ProductCategory::factory()->create();
        $brand = ProductBrand::factory()->create();

        $scheduled = FlashSale::factory()->create([
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'is_active' => true,
        ]);

        $finished = FlashSale::factory()->finished()->create();

        $product = Product::factory()
            ->for($category, 'category')
            ->for($brand, 'brand')
            ->create();

        $scheduled->products()->attach($product->id, [
            'flash_price' => 40000,
            'stock_flash' => 10,
            'sold' => 0,
            'max_qty_per_user' => 1,
        ]);

        $finished->products()->attach($product->id, [
            'flash_price' => 30000,
            'stock_flash' => 5,
            'sold' => 5,
            'max_qty_per_user' => 1,
        ]);

        $response = $this->getJson('/api/flash-sale');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
