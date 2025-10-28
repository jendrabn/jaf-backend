<?php

namespace Tests;

use App\Models\Bank;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Shipping;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JMac\Testing\Traits\AdditionalAssertions;

class ApiTestCase extends BaseTestCase
{
    use AdditionalAssertions;

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->cleanDirectory(storage_path('app/public'));
    }

    protected function createUser(?array $data = [], int $count = 1): User|Collection
    {
        $users = User::factory()->count($count)->create($data);

        return $count > 1 ? $users : $users->first();
    }

    protected function createProduct(?array $data = [], int $count = 1): Product|Collection
    {
        $products = Product::factory()->count($count)->create($data);

        return $count > 1 ? $products : $products->first();
    }

    protected function createProductWithSales(?array $data = [], ?array $quantities = [1], ?string $status = Order::STATUS_COMPLETED): Product
    {
        $sequence = [];

        foreach ($quantities as $quantity) {
            $sequence[] = [
                'quantity' => $quantity,
                'order_id' => Order::factory()->for($this->createUser())->create(compact('status'))->id,
            ];
        }

        return Product::factory()
            ->has(OrderItem::factory(count($sequence))->sequence(...$sequence))
            ->create($data);
    }

    protected function createCategory(?array $data = [], int $count = 1): ProductCategory|Collection
    {
        $categories = ProductCategory::factory()->count($count)->create($data);

        return $count > 1 ? $categories : $categories->first();
    }

    protected function createBrand(?array $data = [], int $count = 1): ProductBrand|Collection
    {
        $brands = ProductBrand::factory()->count($count)->create($data);

        return $count > 1 ? $brands : $brands->first();
    }

    public function createOrder(?array $data = [], ?int $count = 1): Order|Collection
    {
        $orders = Order::factory()->count($count)->for($this->createUser())->create($data);

        return $count > 1 ? $orders : $orders->first();
    }

    protected function formatUserData(User $data): array
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'sex' => $data['sex'],
            'birth_date' => $data['birth_date'],
        ];
    }


    protected function formatCategoryData(ProductCategory|Collection $data): array
    {
        return $data instanceof Collection
            ? $data->map(fn($data) => $this->formatCategoryData($data))->values()->toArray()
            : [
                'id' => $data['id'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'products_count' => $data['products_count'],
            ];
    }

    protected function formatBrandData(ProductBrand|Collection $data): array
    {
        return $data instanceof Collection
            ? $data->map(fn($data) => $this->formatBrandData($data))->values()->toArray()
            : [
                'id' => $data['id'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'products_count' => $data['products_count'],
            ];
    }

    protected function formatCartData(Cart|Collection $data): array
    {
        return $data instanceof Collection
            ? $data->map(fn($data) => $this->formatCartData($data))->values()->toArray()
            : [
                'id' => $data['id'],
                'product' => $this->formatProductData($data['product']),
                'quantity' => $data['quantity'],
            ];
    }

    protected function formatBankData(Bank|Collection $data): array
    {
        return $data instanceof Collection
            ? $data->map(fn($data) => $this->formatBankData($data))->values()->toArray()
            : [
                'id' => $data['id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'logo' => $data['logo'] ? $data['logo']->getUrl() : null,
            ];
    }

    protected function formatUserAddressData(UserAddress $data): array
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'province' => $this->formatProvinceData($data['city']['province']),
            'city' => $this->formatCityData($data['city']),
            'district' => $data['district'],
            'postal_code' => $data['postal_code'],
            'address' => $data['address'],
        ];
    }

    protected function formatProductData(Product|Collection $data): array
    {
        return $data instanceof Collection
            ? $data->map(fn($data) => $this->formatProductData($data))->values()->toArray()
            : [
                'id' => $data['id'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'image' => $data['image'] ? $data['image']->getUrl() : null,
                'category' => $this->formatCategoryData($data['category']),
                'brand' => $this->formatBrandData($data['brand']),
                'sex' => $data['sex'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'weight' => $data['weight'],
                'sold_count' => $data['sold_count'] ?? 0,
                'is_wishlist' => $data['is_wishlist'] ?? false,
            ];
    }

    public function fakeHttpRajaOngkir(): void
    {
        $url = config('shop.rajaongkir.base_url') . '/cost';

        Http::fake([
            $url => function (Request $request) {
                if ($request->method() === 'POST') {

                    foreach (Shipping::COURIERS as $courier) {

                        if ($request->data()['courier'] === $courier) {
                            $file = file_get_contents(
                                base_path('tests/fixtures/rajaongkir/' . $courier . '.json')
                            );

                            return Http::response($file);
                        }
                    }
                }
            },
        ]);
    }
}
