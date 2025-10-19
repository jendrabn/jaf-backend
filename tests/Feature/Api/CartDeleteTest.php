<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\DeleteCartRequest;
use App\Models\Cart;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class CartDeleteTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function delete_cart_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(CartController::class, 'delete', DeleteCartRequest::class);
    }

    #[Test]
    public function delete_cart_request_has_the_correct_validation_rules()
    {
        $user = $this->createUser();
        $rules = (new DeleteCartRequest)->setUserResolver(fn () => $user)->rules();

        $this->assertValidationRules([
            'cart_ids' => [
                'required',
                'array',
            ],
            'cart_ids.*' => [
                'required',
                'integer',
                Rule::exists('carts', 'id')->where('user_id', $user->id),
            ],
        ], $rules);
    }

    #[Test]
    public function unauthenticated_user_cannot_delete_carts()
    {
        $response = $this->deleteJson('/api/carts');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_delete_carts()
    {
        $this->seed(ProductCategorySeeder::class);

        $user = $this->createUser();
        $carts = Cart::factory(2)->sequence(
            ['product_id' => $this->createProduct()->id],
            ['product_id' => $this->createProduct()->id],
        )
            ->for($user)
            ->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/carts', [
            'cart_ids' => $carts->pluck('id'),
        ]);

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertDatabaseEmpty('carts');
    }
}
