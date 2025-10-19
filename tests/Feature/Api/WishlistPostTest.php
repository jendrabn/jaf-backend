<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\WishlistController;
use App\Http\Requests\Api\CreateWishlistRequest;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class WishlistPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_wishlist_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            WishlistController::class,
            'create',
            CreateWishlistRequest::class
        );
    }

    #[Test]
    public function create_wishlist_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_publish', true),
            ],
        ], (new CreateWishlistRequest)->rules());
    }

    #[Test]
    public function unauthenticated_user_cannot_add_product_to_wishlist()
    {
        $response = $this->postJson('/api/wishlist');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_add_product_to_wishlist()
    {
        $this->seed(ProductCategorySeeder::class);

        $user = $this->createUser();
        $product = $this->createProduct();

        $data = ['product_id' => $product->id];

        Sanctum::actingAs($user);

        $response1 = $this->postJson('/api/wishlist', $data);

        $response1->assertCreated()
            ->assertJson(['data' => true]);

        $this->assertDatabaseCount('wishlists', 1)
            ->assertDatabaseHas('wishlists', ['user_id' => $user->id, ...$data]);

        $response2 = $this->postJson('/api/wishlist', $data);

        $response2->assertCreated()
            ->assertJson(['data' => true]);

        $this->assertDatabaseCount('wishlists', 1)
            ->assertDatabaseHas('wishlists', ['user_id' => $user->id, ...$data]);
    }
}
