<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class CartGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_get_all_carts()
    {
        $response = $this->getJson('/api/carts');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_get_all_carts()
    {
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

        $user = $this->createUser();
        $carts = Cart::factory(3)->sequence(
            ['product_id' => $this->createProduct()->id],
            ['product_id' => $this->createProduct()->id],
            ['product_id' => $this->createProduct()->id],
        )
            ->for($user)
            ->create();

        $expectedCarts = $carts->sortByDesc('id')->values();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/carts');

        $response->assertOk()
            ->assertJson([
                'data' => $expectedCarts->map(fn ($item) => [
                    'id' => $item->id,
                    'product' => $this->formatProductData($item->product),
                    'quantity' => $item->quantity,
                ])->toArray(),
            ]);
    }
}
