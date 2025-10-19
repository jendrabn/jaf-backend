<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Models\Cart;
use App\Models\User;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class CartPutTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductCategorySeeder::class);
        $this->user = $this->createUser();
    }

    #[Test]
    public function update_cart_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            CartController::class,
            'update',
            UpdateCartRequest::class
        );
    }

    #[Test]
    public function update_cart_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ], (new UpdateCartRequest)->rules());
    }

    #[Test]
    public function unauthenticated_user_cannot_update_cart()
    {
        $response = $this->putJson('/api/carts/1');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_update_cart()
    {
        $cart = Cart::factory()
            ->for($this->createProduct(['stock' => 2]))
            ->for($this->user)
            ->create(['quantity' => 1]);

        $data = ['quantity' => 2];

        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/carts/'.$cart->id, $data);

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertDatabaseCount('carts', 1)
            ->assertDatabaseHas('carts', $data);
    }

    #[Test]
    public function return_not_found_error_if_cart_id_doenot_exists()
    {
        $cart = Cart::factory()
            ->for($this->createProduct())
            ->for($this->user)
            ->create();

        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/carts/'.$cart->id + 1, ['quantity' => 1]);

        $response->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function cannot_update_cart_if_quantity_exceeds_stock()
    {
        $cart = Cart::factory()
            ->for($this->createProduct(['stock' => 1]))
            ->for($this->user)
            ->create(['quantity' => 1]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/carts/'.$cart->id, ['quantity' => 2]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseCount('carts', 1)
            ->assertModelExists($cart);
    }
}
