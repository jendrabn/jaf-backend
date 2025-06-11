<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Models\{
    Bank,
    Cart,
    Invoice,
    Order,
    Payment,
    Product,
    Shipping,
    User
};
use Database\Seeders\{
    BankSeeder,
    CitySeeder,
    ProductCategorySeeder,
    ProvinceSeeder
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\Sanctum;
use Tests\ApiTestCase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
            BankSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class
        ]);
        $this->user = $this->createUser();
        $this->bank = Bank::first();
        $this->data = [
            'shipping_address' => [
                'name' => 'Garfield',
                'phone' => '+6282310009788',
                'city_id' => 154,
                'district' => 'Cipayung',
                'postal_code' => '13845',
                'address' => 'Jl. Belimbing XII No.19'
            ],
            'shipping_courier' => 'jne',
            'shipping_service' => 'REG',
            'payment_method' => 'bank',
            'bank_id' => $this->bank->id,
            'notes' => fake()->sentence()
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
        $rules = (new CreateOrderRequest())->setUserResolver(fn() => $this->user)->rules();

        $this->assertValidationRules([
            'cart_ids' => [
                'required',
                'array',
            ],
            'cart_ids.*' => [
                'required',
                'integer',
                Rule::exists('carts', 'id')->where('user_id', $this->user->id)
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
            'shipping_address.city_id' => [
                'required',
                'integer',
                'exists:cities,id',
            ],
            'shipping_address.district' => [
                'required',
                'string',
                'min:1',
                'max:100',
            ],
            'shipping_address.postal_code' => [
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
                Rule::in(Shipping::COURIERS)
            ],
            'shipping_service' => [
                'required',
                'string'
            ],
            'payment_method' => [
                'required',
                'string',
                'in:bank,ewallet',
            ],
            'bank_id' => [
                'required_if:payment_method,bank',
                'integer',
                'exists:banks,id',
            ],
            'ewallet_id' => [
                'required_if:payment_method,ewallet',
                'integer',
                'exists:ewallets,id'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:200',
            ]
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
        $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
        $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);
        $totalWeight = 1500;
        $totalPrice = 100000;
        $shippingCost = 34000;
        $totalAmount = $totalPrice + $shippingCost;

        $response = $this->attemptToCreateOrder(['cart_ids' => [$cart1->id, $cart2->id]]);

        $this->assertDatabaseCount('orders', 1);

        $order = Order::first();
        $paymentDueDate = $order['created_at']->addDays(1);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'total_amount' => $totalAmount,
                    'payment_method' => $this->data['payment_method'],
                    'payment_info' => [
                        'name' => $this->bank->name,
                        'code' => $this->bank->code,
                        'account_name' => $this->bank->account_name,
                        'account_number' => $this->bank->account_number
                    ],
                    'payment_due_date' => $paymentDueDate->toISOString(),
                    'created_at' => $order->created_at->toISOString()
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['data']['id'],
            'total_price' => $totalPrice,
            'shipping_cost' => $shippingCost,
            'notes' => $this->data['notes'],
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        // $cart1
        $this->assertDatabaseHas('order_items', [
            'order_id' => $response['data']['id'],
            'product_id' => $cart1->product->id,
            'name' => $cart1->product->name,
            'weight' => $cart1->product->weight,
            'price' => $cart1->product->price,
            'quantity' => $cart1->quantity,
        ]);

        // $cart2
        $this->assertDatabaseHas('order_items', [
            'order_id' => $response['data']['id'],
            'product_id' => $cart2->product->id,
            'name' => $cart2->product->name,
            'weight' => $cart2->product->weight,
            'price' => $cart2->product->price,
            'quantity' => $cart2->quantity,
        ]);

        $this->assertDatabaseHas('invoices', [
            'order_id' => $response['data']['id'],
            'number' => 'INV' . $order->created_at->format('dmy') . $response['data']['id'],
            'amount' => $totalAmount,
            'status' => Invoice::STATUS_UNPAID,
            'due_date' => $paymentDueDate,
        ]);

        $this->assertDatabaseHas('payments', [
            'method' => $this->data['payment_method'],
            'info' => json_encode([
                'name' => $this->bank->name,
                'code' => $this->bank->code,
                'account_name' => $this->bank->account_name,
                'account_number' => $this->bank->account_number
            ]),
            'amount' => $totalAmount,
            'status' => Payment::STATUS_PENDING
        ]);

        $this->assertDatabaseHas('shippings', [
            'order_id' => $response['data']['id'],
            'address' => json_encode([
                'name' => $this->data['shipping_address']['name'],
                'phone' => $this->data['shipping_address']['phone'],
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Timur',
                'district' => $this->data['shipping_address']['district'],
                'postal_code' => $this->data['shipping_address']['postal_code'],
                'address' => $this->data['shipping_address']['address']
            ]),
            'courier' => $this->data['shipping_courier'],
            'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
            'service' => $this->data['shipping_service'],
            'service_name' => 'Layanan Reguler',
            'etd' => '1-2 hari',
            'weight' => $totalWeight,
            'status' => Shipping::STATUS_PENDING
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart1->id]);
        $this->assertDatabaseMissing('carts', ['id' => $cart2->id]);

        $this->assertDatabaseHas('products', [
            'id' => $cart1->product->id,
            'stock' => 3
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $cart2->product->id,
            'stock' => 4
        ]);
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
            'shipping_service' => 'INVALID'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['shipping_service']);
    }
}
