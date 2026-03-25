<?php

namespace Tests\Feature\Seeders;

use App\Models\Bank;
use App\Models\Blog;
use App\Models\Campaign;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\CouponProduct;
use App\Models\CouponUsage;
use App\Models\Courier;
use App\Models\Ewallet;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Subscriber;
use App\Models\Tax;
use App\Models\User;
use Database\Factories\FactoryData;
use Database\Seeders\BankSeeder;
use Database\Seeders\CouponProductSeeder;
use Database\Seeders\CouponSeeder;
use Database\Seeders\CouponUsageSeeder;
use Database\Seeders\CourierSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EwalletSeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeederRefactorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function database_seeder_populates_demo_storefront_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@jaf.test']);
        $this->assertDatabaseHas('users', ['email' => 'customer@jaf.test']);
        $this->assertGreaterThanOrEqual(2, User::query()->count());
        $this->assertGreaterThanOrEqual(40, Product::query()->count());
        $this->assertGreaterThanOrEqual(1, Product::query()->where('is_publish', false)->count());
        $this->assertGreaterThanOrEqual(1, Product::query()->where('stock', 0)->count());
        $this->assertGreaterThanOrEqual(1, Coupon::query()->count());
        $this->assertGreaterThanOrEqual(1, CouponProduct::query()->count());
        $this->assertGreaterThanOrEqual(1, Order::query()->count());
        $this->assertGreaterThanOrEqual(1, CouponUsage::query()->count());
        $this->assertGreaterThanOrEqual(1, Blog::query()->count());
        $this->assertGreaterThanOrEqual(1, ContactMessage::query()->count());
        $this->assertGreaterThanOrEqual(1, Subscriber::query()->count());
        $this->assertGreaterThanOrEqual(1, Campaign::query()->count());
        $this->assertGreaterThanOrEqual(1, Tax::query()->count());
        $this->assertSame(0, DB::table('media')->count());
    }

    #[Test]
    public function reference_and_coupon_seeders_are_idempotent_and_usable_standalone(): void
    {
        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            EwalletSeeder::class,
            CourierSeeder::class,
            CouponSeeder::class,
            CouponProductSeeder::class,
            CouponUsageSeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            EwalletSeeder::class,
            CourierSeeder::class,
        ]);

        $this->assertSame(count(FactoryData::productCategoryNames()), ProductCategory::query()->count());
        $this->assertSame(count(FactoryData::brandNames()), ProductBrand::query()->count());
        $this->assertSame(count(FactoryData::paymentBanks()), Bank::query()->count());
        $this->assertSame(count(FactoryData::ewalletDefinitions()), Ewallet::query()->count());
        $this->assertSame(count(FactoryData::courierDefinitions()), Courier::query()->count());
        $this->assertGreaterThanOrEqual(1, Coupon::query()->count());
        $this->assertGreaterThanOrEqual(1, CouponProduct::query()->count());
        $this->assertGreaterThanOrEqual(1, CouponUsage::query()->count());
    }
}
