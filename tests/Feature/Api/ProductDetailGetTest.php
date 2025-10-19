<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class ProductDetailGetTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
    }

    #[Test]
    public function can_get_product_by_id()
    {
        $product = Product::factory()->has(
            OrderItem::factory(2)
                ->sequence(
                    [
                        'order_id' => $this->createOrder(['status' => Order::STATUS_COMPLETED])->id,
                        'quantity' => 2,
                    ],
                    [
                        'order_id' => $this->createOrder(['status' => Order::STATUS_COMPLETED])->id,
                        'quantity' => 3,
                    ]
                )
        )
            ->hasImages(2)
            ->create();

        $expectedImages = $product->images
            ? $product->images->map(fn ($media) => $media->getUrl())->toArray()
            : [];

        $response = $this->getJson('/api/products/'.$product->id);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'images' => $expectedImages,
                    'category' => $this->formatCategoryData($product->category),
                    'description' => $product->description,
                    'brand' => $this->formatBrandData($product->brand),
                    'sex' => $product->sex,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'weight' => $product->weight,
                    'sold_count' => 5,
                    'is_wishlist' => false,
                ],
            ])
            ->assertJsonCount(2, 'data.images');
    }

    #[Test]
    public function returns_not_found_error_if_product_id_doenot_exist()
    {
        $product = $this->createProduct();

        $response = $this->getJson('/api/products/'.$product->id + 1);

        $response->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function returns_not_found_error_if_product_is_not_published()
    {
        $product = $this->createProduct(['is_publish' => false]);

        $response = $this->getJson('/api/products/'.$product->id);

        $response->assertNotFound()
            ->assertJsonStructure(['message']);
    }
}
