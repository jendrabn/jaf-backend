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
            ->assertJsonPath('data.0.products.0.flash_price_display', '50.000')
            ->assertJsonPath('data.0.products.0.flash_stock_remaining', 20)
            ->assertJsonPath('data.0.products.1.flash_stock_remaining', 20)
            ->assertJsonPath('data.0.products.1.max_qty_per_user', 2);

        $productResponse = $this->getJson('/api/products/'.$products->first()->slug);

        $productResponse->assertOk()
            ->assertJsonPath('data.flash_sale_price', 50000)
            ->assertJsonPath('data.is_in_flash_sale', true)
            ->assertJsonPath('data.flash_sale_end_at', $flashSale->end_at->format('Y-m-d H:i:s'));
    }

    public function test_it_returns_one_running_and_two_scheduled_flash_sales_when_available(): void
    {
        $runningBase = now();
        $running = FlashSale::factory()
            ->running()
            ->count(3)
            ->sequence(
                ...collect([30, 10, 20])
                    ->map(fn (int $minutes) => [
                        'end_at' => $runningBase->copy()->addMinutes($minutes),
                    ])
                    ->all()
            )
            ->create();

        $scheduledBase = now();
        $scheduled = FlashSale::factory()
            ->count(4)
            ->sequence(
                ...collect([2, 1, 3, 4])
                    ->map(fn (int $hours) => [
                        'start_at' => $scheduledBase->copy()->addHours($hours),
                        'end_at' => $scheduledBase->copy()->addHours($hours + 1),
                        'is_active' => true,
                    ])
                    ->all()
            )
            ->create();

        $response = $this->getJson('/api/flash-sale');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.status', 'running')
            ->assertJsonPath('data.1.status', 'scheduled')
            ->assertJsonPath('data.2.status', 'scheduled');

        $expectedOrder = $running->sortBy('end_at')
            ->take(1)
            ->pluck('id')
            ->concat(
                $scheduled->sortBy('start_at')
                    ->take(2)
                    ->pluck('id')
            )
            ->values()
            ->all();

        $this->assertSame(
            $expectedOrder,
            collect($response->json('data'))->pluck('id')->all()
        );
    }

    public function test_it_returns_only_scheduled_flash_sales_when_no_running_available(): void
    {
        $scheduledBase = now();
        $scheduled = FlashSale::factory()
            ->count(3)
            ->sequence(
                ...collect([3, 1, 2])
                    ->map(fn (int $hours) => [
                        'start_at' => $scheduledBase->copy()->addHours($hours),
                        'end_at' => $scheduledBase->copy()->addHours($hours + 1),
                        'is_active' => true,
                    ])
                    ->all()
            )
            ->create();

        FlashSale::factory()->finished()->count(2)->create();

        $response = $this->getJson('/api/flash-sale');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $data = collect($response->json('data'));

        $this->assertTrue(
            $data->pluck('status')->every(fn (string $status) => $status === 'scheduled')
        );

        $this->assertSame(
            $scheduled->sortBy('start_at')->take(2)->pluck('id')->values()->all(),
            $data->pluck('id')->all()
        );
    }

    public function test_scheduled_flash_sale_products_have_masked_price_display(): void
    {
        $category = ProductCategory::factory()->create();
        $brand = ProductBrand::factory()->create();

        $scheduled = FlashSale::factory()->create([
            'start_at' => now()->addDays(1),
            'end_at' => now()->addDays(1)->addHours(2),
            'is_active' => true,
        ]);

        $product = Product::factory()
            ->for($category, 'category')
            ->for($brand, 'brand')
            ->create();

        $scheduled->products()->attach($product->id, [
            'flash_price' => 254000,
            'stock_flash' => 10,
            'sold' => 0,
            'max_qty_per_user' => 1,
        ]);

        $response = $this->getJson('/api/flash-sale');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'scheduled')
            ->assertJsonPath('data.0.products.0.flash_price', 254000)
            ->assertJsonPath('data.0.products.0.flash_price_display', '?54.000')
            ->assertJsonPath('data.0.products.0.is_flash_price_masked', true);
    }
}
