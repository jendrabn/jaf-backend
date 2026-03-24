<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\ProductRatingRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductRating;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderRatingsPostTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
        ]);
    }

    #[Test]
    public function add_rating_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            OrderController::class,
            'addRating',
            ProductRatingRequest::class
        );
    }

    #[Test]
    public function product_rating_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'ratings' => [
                'required',
                'array',
            ],
            'ratings.*.order_item_id' => [
                'required',
                'numeric',
                'exists:order_items,id',
            ],
            'ratings.*.rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5',
            ],
            'ratings.*.comment' => [
                'nullable',
                'string',
                'min:3',
                'max:15000',
            ],
            'ratings.*.is_anonymous' => [
                'nullable',
                'boolean',
            ],
        ], (new ProductRatingRequest)->rules());
    }

    #[Test]
    public function unauthenticated_user_cannot_add_ratings()
    {
        $response = $this->postJson('/api/orders/ratings');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_add_ratings_for_order_items()
    {
        $user = $this->createUser();
        $order = Order::factory()->for($user)->create();
        $orderItem = OrderItem::factory()->for($order)->for($this->createProduct())->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders/ratings', [
            'ratings' => [
                [
                    'order_item_id' => $orderItem->id,
                    'rating' => 5,
                    'comment' => 'Produk sangat bagus',
                    'is_anonymous' => false,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertDatabaseHas('product_ratings', [
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'comment' => 'Produk sangat bagus',
            'is_anonymous' => false,
        ]);
    }

    #[Test]
    public function can_update_existing_rating()
    {
        $user = $this->createUser();
        $order = Order::factory()->for($user)->create();
        $orderItem = OrderItem::factory()->for($order)->for($this->createProduct())->create();

        ProductRating::query()->create([
            'order_item_id' => $orderItem->id,
            'rating' => 3,
            'comment' => 'Komentar lama',
            'is_anonymous' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders/ratings', [
            'ratings' => [
                [
                    'order_item_id' => $orderItem->id,
                    'rating' => 4,
                    'comment' => 'Komentar baru',
                    'is_anonymous' => false,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertDatabaseCount('product_ratings', 1);
        $this->assertDatabaseHas('product_ratings', [
            'order_item_id' => $orderItem->id,
            'rating' => 4,
            'comment' => 'Komentar baru',
            'is_anonymous' => false,
        ]);
    }
}
