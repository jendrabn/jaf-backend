<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\CreateCartRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class CartPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_cart_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            CartController::class,
            'create',
            CreateCartRequest::class
        );
    }

    #[Test]
    public function create_cart_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_publish', true),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ], (new CreateCartRequest)->rules());
    }

    #[Test]
    public function unauthenticated_user_cannot_add_product_to_cart()
    {
        $response = $this->postJson('/api/carts');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_add_product_to_cart()
    {
        $this->seed(ProductCategorySeeder::class);

        $product = Product::factory()->create(['stock' => 10]);
        Cart::factory()->for($product)->for(User::factory()->create())->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $data = [
            'product_id' => $product->id,
            'quantity' => 3,
        ];

        $response1 = $this->postJson('/api/carts', $data);

        $response1->assertCreated()
            ->assertJson(['data' => true]);

        $this->assertDatabaseCount('carts', 2)
            ->assertDatabaseHas('carts', ['user_id' => $user->id, ...$data]);

        $response2 = $this->postJson('/api/carts', $data);

        $response2->assertCreated()
            ->assertJson(['data' => true]);

        $this->assertDatabaseCount('carts', 2)
            ->assertDatabaseHas('carts', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $data['quantity'] * 2,
            ]);
    }

    #[Test]
    public function cannot_add_product_to_cart_if_quantity_exceeds_stock()
    {
        $this->seed(ProductCategorySeeder::class);

        $product = Product::factory()->create(['stock' => 5]);
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $cart = Cart::factory()->for($product)->for($user)->create(['quantity' => 3]);

        $response = $this->postJson('/api/carts', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseCount('carts', 1)
            ->assertModelExists($cart);
    }
}
