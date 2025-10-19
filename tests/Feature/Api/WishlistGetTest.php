<?php

namespace Tests\Feature\Api;

use App\Models\Wishlist;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class WishlistGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_get_all_wishlist()
    {
        $response = $this->getJson('/api/wishlist');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_get_all_wishlist()
    {
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

        $user = $this->createUser();

        Wishlist::factory()
            ->for($this->createProduct(['is_publish' => false]))
            ->for($user)
            ->create();

        $wishlists = Wishlist::factory(3)
            ->sequence(
                ['product_id' => $this->createProduct()->id],
                ['product_id' => $this->createProduct()->id],
                ['product_id' => $this->createProduct()->id],
            )
            ->for($user)
            ->create();

        $expectedWishlists = $wishlists->sortByDesc('id')->values();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wishlist');

        $response->assertOk()
            ->assertJson([
                'data' => $expectedWishlists->map(fn ($item) => [
                    'id' => $item->id,
                    'product' => $this->formatProductData($item->product),
                ])->toArray(),
            ])->assertJsonCount(3, 'data');
    }
}
