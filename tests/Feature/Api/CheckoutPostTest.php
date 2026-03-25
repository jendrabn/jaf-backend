<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Api\CheckoutRequest;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Ewallet;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\BankSeeder;
use Database\Seeders\CourierSeeder;
use Database\Seeders\EwalletSeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
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

        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            EwalletSeeder::class,
            CourierSeeder::class,
        ]);

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

    private function createUserAddress(): UserAddress
    {
        $this->fakeRajaOngkirApi();

        return UserAddress::query()->create([
            'user_id' => $this->user->id,
            'province_id' => 11,
            'city_id' => 154,
            'district_id' => 2550,
            'subdistrict_id' => 3500,
            'name' => 'Garfield',
            'phone' => '081234567890',
            'zip_code' => '13845',
            'address' => 'Jl. Belimbing XII No.19',
        ]);
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
        $userAddress = $this->createUserAddress();
        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);

        $this->banks->first()
            ?->addMedia(UploadedFile::fake()->image('bank.jpg'))
            ->toMediaCollection(Bank::MEDIA_COLLECTION_NAME);

        $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);

        $response->assertOk()
            ->assertJsonPath('data.shipping_address.id', $userAddress->id)
            ->assertJsonPath('data.shipping_address.province.name', 'DKI Jakarta')
            ->assertJsonPath('data.shipping_address.city.name', 'Jakarta Timur')
            ->assertJsonPath('data.shipping_address.district.name', 'Cipayung')
            ->assertJsonPath('data.shipping_address.subdistrict.name', 'Cilangkap')
            ->assertJsonPath('data.shipping_address.zip_code', '13845')
            ->assertJsonPath('data.total_quantity', 3)
            ->assertJsonPath('data.total_weight', 1500)
            ->assertJsonPath('data.total_price', 100000)
            ->assertJsonPath('data.payment_methods.gateway.provider', 'midtrans')
            ->assertJsonPath('data.payment_methods.gateway.fee', (int) config('services.midtrans.fee_flat', 0))
            ->assertJsonCount(2, 'data.carts')
            ->assertJsonCount(1, 'data.shipping_methods')
            ->assertJsonCount($this->banks->count(), 'data.payment_methods.bank')
            ->assertJsonCount(Ewallet::query()->count(), 'data.payment_methods.ewallet')
            ->assertJsonFragment([
                'courier' => 'jne',
                'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
                'service' => 'REG',
                'service_name' => 'Layanan Reguler',
                'cost' => 34000,
                'etd' => '1-2 hari',
            ]);

        $bankWithLogo = collect($response['data']['payment_methods']['bank'])->firstWhere('id', $this->banks->first()?->id);

        $this->assertStringStartsWith('http', $bankWithLogo['logo']);
    }

    #[Test]
    public function can_checkout_without_a_shipping_address()
    {
        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);

        $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);

        $response->assertOk()
            ->assertJsonPath('data.shipping_address', null)
            ->assertJsonPath('data.total_quantity', 3)
            ->assertJsonPath('data.total_weight', 1500)
            ->assertJsonPath('data.total_price', 100000)
            ->assertJsonCount(2, 'data.carts')
            ->assertJsonCount(0, 'data.shipping_methods')
            ->assertJsonCount($this->banks->count(), 'data.payment_methods.bank')
            ->assertJsonCount(Ewallet::query()->count(), 'data.payment_methods.ewallet');
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
