<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Tax;
use App\Models\User;
use Database\Seeders\BankSeeder;
use Database\Seeders\CourierSeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class OrderPostTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    private Bank $bank;

    private array $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            CourierSeeder::class,
        ]);

        $this->fakeRajaOngkirApi();

        $this->user = $this->createUser();
        $this->bank = Bank::firstOrFail();
        $this->data = [
            'shipping_address' => [
                'name' => 'Garfield',
                'phone' => '081234567890',
                'province_id' => 11,
                'city_id' => 154,
                'district_id' => 2550,
                'subdistrict_id' => 3500,
                'zip_code' => '13845',
                'address' => 'Jl. Belimbing XII No.19',
            ],
            'shipping_courier' => 'jne',
            'shipping_service' => 'REG',
            'payment_method' => 'bank',
            'bank_id' => $this->bank->id,
            'note' => fake()->sentence(),
        ];
    }

    private function createCart(int $quantity = 1, ?array $productData = []): Cart
    {
        return Cart::factory()
            ->for(Product::factory()->create($productData))
            ->for($this->user)
            ->create(['quantity' => $quantity]);
    }

    private function attemptToCreateOrder(array $data = []): TestResponse
    {
        Sanctum::actingAs($this->user);

        return $this->postJson('/api/orders', array_merge($this->data, $data));
    }

    #[Test]
    public function create_order_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            OrderController::class,
            'create',
            CreateOrderRequest::class
        );
    }

    #[Test]
    public function create_order_request_uses_the_correct_validation_rules()
    {
        $rules = (new CreateOrderRequest)->setUserResolver(fn () => $this->user)->rules();

        $this->assertValidationRules([
            'cart_ids' => [
                'required',
                'array',
            ],
            'cart_ids.*' => [
                'required',
                'integer',
                Rule::exists('carts', 'id')->where('user_id', $this->user->id),
            ],
            'shipping_address.name' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'shipping_address.phone' => [
                'required',
                'string',
                'starts_with:08,62,+62',
                'min:10',
                'max:15',
            ],
            'shipping_address.province_id' => [
                'required',
                'integer',
            ],
            'shipping_address.city_id' => [
                'required',
                'integer',
            ],
            'shipping_address.district_id' => [
                'required',
                'integer',
            ],
            'shipping_address.subdistrict_id' => [
                'required',
                'integer',
            ],
            'shipping_address.zip_code' => [
                'required',
                'string',
                'min:5',
                'max:5',
            ],
            'shipping_address.address' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'shipping_courier' => [
                'required',
                'string',
                'exists:couriers,code',
            ],
            'shipping_service' => [
                'required',
                'string',
            ],
            'payment_method' => [
                'required',
                'string',
                'in:bank,ewallet,gateway',
            ],
            'bank_id' => [
                'required_if:payment_method,bank',
                'integer',
                'exists:banks,id',
            ],
            'ewallet_id' => [
                'required_if:payment_method,ewallet',
                'integer',
                'exists:ewallets,id',
            ],
            'note' => [
                'nullable',
                'string',
                'max:200',
            ],
            'coupon_code' => [
                'nullable',
                'string',
                'exists:coupons,code',
            ],
        ], $rules);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_order()
    {
        $response = $this->postJson('/api/orders');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_create_order()
    {
        Tax::query()->create([
            'name' => 'PPN',
            'rate' => 0,
        ]);

        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);

        $response = $this->attemptToCreateOrder(['cart_ids' => [$cart1->id, $cart2->id]]);

        $this->assertDatabaseCount('orders', 1);

        $order = Order::query()->with(['invoice.payment', 'shipping'])->firstOrFail();

        $response->assertCreated()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.total_amount', 134000)
            ->assertJsonPath('data.payment_method', 'bank')
            ->assertJsonPath('data.payment_info.name', $this->bank->name)
            ->assertJsonPath('data.gateway_fee', 0)
            ->assertJsonPath('data.gateway_fee_name', null);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total_price' => 100000,
            'shipping_cost' => 34000,
            'tax_amount' => 0,
            'note' => $this->data['note'],
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $cart1->product->id,
            'name' => $cart1->product->name,
            'weight' => $cart1->product->weight,
            'price' => $cart1->product->price,
            'quantity' => $cart1->quantity,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $cart2->product->id,
            'name' => $cart2->product->name,
            'weight' => $cart2->product->weight,
            'price' => $cart2->product->price,
            'quantity' => $cart2->quantity,
        ]);

        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'amount' => 134000,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $order->invoice->id,
            'method' => Payment::METHOD_BANK,
            'amount' => 134000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('shippings', [
            'order_id' => $order->id,
            'courier' => 'jne',
            'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
            'service' => 'REG',
            'service_name' => 'Layanan Reguler',
            'etd' => '1-2 hari',
            'weight' => 1500,
            'status' => Shipping::STATUS_PENDING,
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart1->id]);
        $this->assertDatabaseMissing('carts', ['id' => $cart2->id]);

        $this->assertDatabaseHas('products', [
            'id' => $cart1->product->id,
            'stock' => 3,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $cart2->product->id,
            'stock' => 4,
        ]);

        $this->assertSame('DKI Jakarta', $order->shipping->address['province']);
        $this->assertSame('Jakarta Timur', $order->shipping->address['city']);
        $this->assertSame('Cipayung', $order->shipping->address['district']);
        $this->assertSame('Cilangkap', $order->shipping->address['subdistrict']);
        $this->assertSame('13845', $order->shipping->address['zip_code']);
    }

    #[Test]
    public function cannot_create_order_if_product_is_not_published()
    {
        $cart1 = $this->createCart(1, ['stock' => 1, 'weight' => 100, 'is_publish' => false]);
        $cart2 = $this->createCart(3, ['stock' => 3, 'weight' => 100]);

        $response = $this->attemptToCreateOrder(['cart_ids' => [$cart1->id, $cart2->id]]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart_ids']);
    }

    #[Test]
    public function cannot_create_order_if_quantity_exceeds_stock()
    {
        $cart1 = $this->createCart(3, ['stock' => 1, 'weight' => 100]);
        $cart2 = $this->createCart(1, ['stock' => 3, 'weight' => 100]);

        $response = $this->attemptToCreateOrder(['cart_ids' => [$cart1->id, $cart2->id]]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart_ids']);
    }

    #[Test]
    public function cannot_create_order_if_total_weight_exceeds_25kg()
    {
        $cart1 = $this->createCart(2, ['stock' => 5, 'weight' => 3000]);
        $cart2 = $this->createCart(2, ['stock' => 5, 'weight' => 10000]);

        $response = $this->attemptToCreateOrder(['cart_ids' => [$cart1->id, $cart2->id]]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart_ids']);
    }

    #[Test]
    public function cannot_create_order_if_shipping_service_doenot_exist()
    {
        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);

        $response = $this->attemptToCreateOrder([
            'cart_ids' => [$cart1->id, $cart2->id],
            'shipping_service' => 'INVALID',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['shipping_service']);
    }
}
