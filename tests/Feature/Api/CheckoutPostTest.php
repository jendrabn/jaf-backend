<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Api\CheckoutRequest;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\BankSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class CheckoutPostTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    private Collection $banks;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class, BankSeeder::class]);
        $this->user = $this->createUser();
        $this->banks = Bank::all();
    }

    private function createCart(int $quantity = 1, ?array $productData = []): Cart
    {
        return Cart::factory()
            ->for($this->createProduct($productData))
            ->for($this->user)
            ->create(['quantity' => $quantity]);
    }

    private function attemptToCheckout(array $cartIds = []): TestResponse
    {
        Sanctum::actingAs($this->user);

        return $this->postJson('/api/checkout', [
            'cart_ids' => $cartIds,
        ]);
    }

    #[Test]
    public function checkout_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            CheckoutController::class,
            'checkout',
            CheckoutRequest::class
        );
    }

    #[Test]
    public function checkout_request_has_the_correct_validation_rules()
    {
        $rules = (new CheckoutRequest)->setUserResolver(fn () => $this->user)->rules();

        $this->assertValidationRules([
            'cart_ids' => [
                'required',
                'array',
            ],
            'cart_ids.*' => [
                'integer',
                Rule::exists('carts', 'id')->where('user_id', $this->user->id),
            ],
        ], $rules);
    }

    #[Test]
    public function unauthenticated_user_cannot_checkout()
    {
        $response = $this->postJson('/api/checkout');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_checkout()
    {
        $this->seed([ProvinceSeeder::class, CitySeeder::class]);

        $userAddress = UserAddress::factory()
            ->for($this->user)
            ->create(['city_id' => 154]);
        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);
        $totalWeight = 1500;
        $totalQuantity = 3;
        $totalPrice = 100000;
        // Bank Logo
        $this->banks[0]
            ->addMedia(UploadedFile::fake()->image('bank.jpg'))
            ->toMediaCollection(Bank::MEDIA_COLLECTION_NAME);

        $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'shipping_address' => $this->formatUserAddressData($userAddress),
                    'carts' => [
                        $this->formatCartData($cart1),
                        $this->formatCartData($cart2),
                    ],
                    'payment_methods' => [
                        'bank' => $this->formatBankData($this->banks),
                    ],
                    'total_quantity' => $totalQuantity,
                    'total_weight' => $totalWeight,
                    'total_price' => $totalPrice,
                ],
            ])
            ->assertJsonFragment([
                'courier' => 'jne',
                'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
                'service' => 'REG',
                'service_name' => 'Layanan Reguler',
                'cost' => 34000,
                'etd' => '1-2 hari',
            ])
            ->assertJsonCount(12, 'data.shipping_methods')
            ->assertJsonCount(1, 'data.payment_methods.bank');

        $this->assertStringStartsWith('http', $response['data']['payment_methods']['bank'][0]['logo']);
    }

    #[Test]
    public function can_checkout_without_a_shipping_address()
    {
        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);
        $totalWeight = 1500;
        $totalQuantity = 3;
        $totalPrice = 100000;

        $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'shipping_address' => null,
                    'carts' => [
                        $this->formatCartData($cart1),
                        $this->formatCartData($cart2),
                    ],
                    'shipping_methods' => null,
                    'payment_methods' => [
                        'bank' => $this->formatBankData($this->banks),
                    ],
                    'total_quantity' => $totalQuantity,
                    'total_weight' => $totalWeight,
                    'total_price' => $totalPrice,
                ],
            ])
            ->assertJsonCount(1, 'data.payment_methods.bank');
    }

    #[Test]
    public function cannot_checkout_if_product_is_not_published()
    {
        $cart1 = $this->createCart(1, ['stock' => 1, 'weight' => 100, 'is_publish' => false]);
        $cart2 = $this->createCart(3, ['stock' => 3, 'weight' => 100]);

        $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart_ids']);
    }

    #[Test]
    public function cannot_checkout_if_quantity_exceeds_stock()
    {
        $cart1 = $this->createCart(3, ['stock' => 1, 'weight' => 100]);
        $cart2 = $this->createCart(1, ['stock' => 3, 'weight' => 100]);

        $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart_ids']);
    }

    #[Test]
    public function cannot_checkout_if_total_weight_exceeds_25kg()
    {
        $cart1 = $this->createCart(2, ['stock' => 5, 'weight' => 3000]);
        $cart2 = $this->createCart(2, ['stock' => 5, 'weight' => 10000]);

        $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart_ids']);
    }
}
