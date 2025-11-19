<?php

namespace Tests\Unit;

use App\Models\FlashSale;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_metadata_is_calculated_from_schedule(): void
    {
        $scheduled = FlashSale::factory()->create([
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
        ]);

        $running = FlashSale::factory()->create([
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
        ]);

        $finished = FlashSale::factory()->finished()->create();

        $this->assertSame('scheduled', $scheduled->status);
        $this->assertSame('Scheduled', $scheduled->status_label);
        $this->assertSame('info', $scheduled->status_color);

        $this->assertSame('running', $running->status);
        $this->assertSame('Running', $running->status_label);
        $this->assertSame('success', $running->status_color);

        $this->assertSame('finished', $finished->status);
        $this->assertSame('Finished', $finished->status_label);
        $this->assertSame('secondary', $finished->status_color);
    }

    public function test_flash_sale_relationships_store_pivot_data(): void
    {
        $category = ProductCategory::factory()->create();
        $brand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->state([
                'product_category_id' => $category->id,
                'product_brand_id' => $brand->id,
            ])
            ->create();

        $flashSale = FlashSale::factory()->create();

        $flashSale->products()->attach($product->id, [
            'flash_price' => '45000.00',
            'stock_flash' => 50,
            'sold' => 10,
            'max_qty_per_user' => 2,
        ]);

        $flashSale->load('products');
        $pivot = $flashSale->products->first()->pivot;

        $this->assertSame(45000, (int) $pivot->flash_price);
        $this->assertSame(50, (int) $pivot->stock_flash);
        $this->assertSame(10, (int) $pivot->sold);
        $this->assertSame(2, (int) $pivot->max_qty_per_user);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'flash_sale_id' => $flashSale->id,
            'name' => $product->name,
            'weight' => 1000,
            'price' => 50000,
            'discount_in_percent' => 10,
            'price_after_discount' => 45000,
            'quantity' => 1,
        ]);

        $this->assertTrue($orderItem->flashSale->is($flashSale));
    }
}
