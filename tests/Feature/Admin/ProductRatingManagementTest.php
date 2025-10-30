<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductRatingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ProductCategory $category;

    protected ProductBrand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('backoffice.access');
        Permission::findOrCreate('products.delete');
        Permission::findOrCreate('products.edit');

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(['backoffice.access', 'products.delete', 'products.edit']);

        $this->actingAs($this->admin);

        $this->category = ProductCategory::factory()->create();
        $this->brand = ProductBrand::factory()->create();
    }

    #[Test]
    public function it_can_delete_a_product_rating(): void
    {
        $product = $this->createProduct();
        $rating = $this->createRatingForProduct($product);

        $this->assertSame($product->id, $rating->orderItem->product_id);
        $this->assertTrue($product->productRatings()->where('product_ratings.id', $rating->id)->exists());

        $response = $this->deleteJson(route('admin.products.ratings.destroy', [$product->id, $rating->id]));

        $response->assertOk()
            ->assertJson(['message' => 'Product rating deleted successfully.']);

        $this->assertDatabaseMissing('product_ratings', [
            'id' => $rating->id,
        ]);
    }

    #[Test]
    public function it_can_mass_delete_product_ratings(): void
    {
        $product = $this->createProduct();
        $ratings = $this->createRatings($product, 3);

        $response = $this->deleteJson(route('admin.products.ratings.massDestroy', $product->id), [
            'ids' => $ratings->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Product ratings deleted successfully.']);

        $ratings->each(function (ProductRating $rating) {
            $this->assertDatabaseMissing('product_ratings', [
                'id' => $rating->id,
            ]);
        });
    }

    #[Test]
    public function it_can_toggle_publish_status(): void
    {
        $product = $this->createProduct();
        $rating = $this->createRatingForProduct($product, ['is_publish' => true]);

        $this->assertSame($product->id, $rating->orderItem->product_id);
        $this->assertTrue($product->productRatings()->where('product_ratings.id', $rating->id)->exists());

        $response = $this->putJson(route('admin.products.ratings.publish', [$product->id, $rating->id]));

        $response->assertOk()
            ->assertJson(['message' => 'Product rating updated successfully.']);

        $this->assertDatabaseHas('product_ratings', [
            'id' => $rating->id,
            'is_publish' => false,
        ]);
    }

    protected function createProduct(): Product
    {
        return Product::factory()
            ->for($this->category, 'category')
            ->for($this->brand, 'brand')
            ->create();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createRatingForProduct(Product $product, array $overrides = []): ProductRating
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create(['status' => Order::STATUS_COMPLETED]);
        $orderItem = OrderItem::factory()->for($order)->for($product)->create();

        return ProductRating::create(array_merge([
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'comment' => 'Great product',
            'is_anonymous' => false,
            'is_publish' => true,
        ], $overrides));
    }

    protected function createRatings(Product $product, int $count): Collection
    {
        return Collection::times($count, function () use ($product) {
            return $this->createRatingForProduct($product);
        });
    }
}
