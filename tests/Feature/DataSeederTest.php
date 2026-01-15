<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class DataSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateEssentialTables();

    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        Schema::disableForeignKeyConstraints();
        Schema::dropAllTables();
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_data_seeder_populates_expected_catalog(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::create(2025, 1, 15, 9, 0, 0, 'Asia/Jakarta')
        );

        config(['seed.attach_media' => false]);

        Artisan::call('db:seed', [
            '--class' => DataSeeder::class,
            '--no-interaction' => true,
        ]);

        $this->assertSame(
            ['Botol Parfum Kosong', 'Parfum', 'Parfum Laundry', 'Pengharum Ruangan', 'Travel & Decant'],
            ProductCategory::query()->pluck('name')->sort()->values()->all()
        );

        $this->assertGreaterThanOrEqual(30, ProductBrand::query()->count());

        $this->assertGreaterThanOrEqual(140, Product::query()->count());
        $this->assertGreaterThanOrEqual(
            100,
            Product::query()->whereHas('category', fn ($q) => $q->where('name', 'Parfum'))->count()
        );
        $this->assertGreaterThanOrEqual(
            10,
            Product::query()->whereHas('category', fn ($q) => $q->where('name', 'Parfum Laundry'))->count()
        );
        $this->assertGreaterThanOrEqual(
            30,
            Product::query()->whereHas('category', fn ($q) => $q->where('name', 'Botol Parfum Kosong'))->count()
        );

        $this->assertSame(10, Product::query()->where('is_publish', false)->count());
        $this->assertSame(10, Product::query()->where('stock', 0)->count());
        $this->assertSame(
            0,
            Product::query()->where('is_publish', false)->where('stock', 0)->count(),
            'Unpublished products must not overlap with out-of-stock selection.'
        );

        $flashSales = FlashSale::query()->orderBy('start_at')->get();
        $this->assertSame(12, $flashSales->count());

        $flashSales->values()->each(function (FlashSale $flashSale, int $index): void {
            $expectedStart = CarbonImmutable::now('Asia/Jakarta')->addDays(30 * ($index + 1));
            $expectedEnd = $expectedStart->addDays(3);

            $this->assertSame(
                $expectedStart->format('Y-m-d'),
                CarbonImmutable::parse($flashSale->start_at)->format('Y-m-d')
            );
            $this->assertSame(
                $expectedEnd->format('Y-m-d'),
                CarbonImmutable::parse($flashSale->end_at)->format('Y-m-d')
            );
        });

        $bleu = Product::query()->where('slug', 'bleu-de-chanel-edp-100ml')->first();
        $this->assertNotNull($bleu);
        $this->assertSame(2_699_000, $bleu->price);

        $baccarat = Product::query()->where('slug', 'baccarat-rouge-540-edp-75ml')->first();
        $this->assertNotNull($baccarat);
        $this->assertSame(5_999_000, $baccarat->price);

        $aventus = Product::query()->where('slug', 'creed-aventus-edp-100ml')->first();
        $this->assertNotNull($aventus);
        $this->assertSame(7_999_000, $aventus->price);

        $sauvage50 = Product::query()->where('slug', 'dior-sauvage-edp-50ml')->first();
        $sauvage100 = Product::query()->where('slug', 'dior-sauvage-edp-100ml')->first();

        $this->assertNotNull($sauvage50);
        $this->assertNotNull($sauvage100);
        $this->assertNotSame($sauvage50->price, $sauvage100->price);
        $this->assertGreaterThan($sauvage50->price, $sauvage100->price);

        $this->assertSame(10, DB::table('banks')->count());
        $this->assertSame(
            ['002', '008', '009', '011', '013', '014', '016', '022', '028', '200'],
            DB::table('banks')->orderBy('code')->pluck('code')->all()
        );

        $this->assertSame(5, DB::table('ewallets')->count());

        $this->assertSame(150, DB::table('orders')->count());
        $this->assertSame(10, Order::query()->where('status', Order::STATUS_PENDING_PAYMENT)->count());
        $this->assertSame(25, Order::query()->where('status', Order::STATUS_PENDING)->count());
        $this->assertSame(30, Order::query()->where('status', Order::STATUS_PROCESSING)->count());
        $this->assertSame(25, Order::query()->where('status', Order::STATUS_ON_DELIVERY)->count());
        $this->assertSame(50, Order::query()->where('status', Order::STATUS_COMPLETED)->count());
        $this->assertSame(10, Order::query()->where('status', Order::STATUS_CANCELLED)->count());

        $this->assertSame(150, DB::table('invoices')->count());
        $this->assertSame(150, DB::table('payments')->count());
        $this->assertSame(150, DB::table('shippings')->count());

        $bankPaymentCount = DB::table('payments')->where('method', 'bank')->count();
        $ewalletPaymentCount = DB::table('payments')->where('method', 'ewallet')->count();
        $this->assertGreaterThanOrEqual(50, $bankPaymentCount);
        $this->assertGreaterThanOrEqual(50, $ewalletPaymentCount);
        $this->assertSame($bankPaymentCount, DB::table('payment_banks')->count());
        $this->assertSame($ewalletPaymentCount, DB::table('payment_ewallets')->count());

        $trackedOrderIds = Order::query()
            ->whereIn('status', [Order::STATUS_ON_DELIVERY, Order::STATUS_COMPLETED])
            ->pluck('id');

        $this->assertSame(
            $trackedOrderIds->count(),
            DB::table('shippings')
                ->whereIn('order_id', $trackedOrderIds)
                ->whereNotNull('tracking_number')
                ->count()
        );

        $shippingSample = DB::table('shippings')->first();
        $this->assertNotNull($shippingSample);
        $snapshot = json_decode($shippingSample->address, true);
        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('name', $snapshot);
        $this->assertArrayHasKey('province', $snapshot);

        $coupons = Coupon::query()->with('products:id')->get();
        $this->assertSame(25, $coupons->count());
        $this->assertSame(5, $coupons->where('promo_type', 'limit')->count());
        $this->assertSame(10, $coupons->where('promo_type', 'period')->count());
        $this->assertSame(10, $coupons->where('promo_type', 'product')->count());
        $this->assertSame(25, $coupons->pluck('code')->unique()->count());
        $this->assertGreaterThan(
            0,
            $coupons->where('discount_type', 'fixed')->count()
        );
        $this->assertGreaterThan(
            0,
            $coupons->where('discount_type', 'percentage')->count()
        );
        $this->assertGreaterThan(
            0,
            $coupons->where('is_active', false)->count()
        );
        $this->assertSame(
            0,
            Coupon::query()->where('promo_type', 'limit')->whereNotNull('start_date')->count()
        );

        $referenceDate = CarbonImmutable::now('Asia/Jakarta')->startOfDay();
        $periodStart = $referenceDate->addDay();
        $periodEnd = $referenceDate->addDays(30);

        Coupon::query()
            ->whereIn('promo_type', ['period', 'product'])
            ->get()
            ->each(function (Coupon $coupon) use ($periodStart, $periodEnd): void {
                $this->assertNotNull($coupon->start_date);
                $this->assertNotNull($coupon->end_date);

                $start = CarbonImmutable::parse($coupon->start_date);
                $end = CarbonImmutable::parse($coupon->end_date);

                $this->assertTrue(
                    $start->greaterThanOrEqualTo($periodStart),
                    'Start date should be at least day +1.'
                );
                $this->assertTrue(
                    $end->lessThanOrEqualTo($periodEnd),
                    'End date should be within +30 days.'
                );
                $this->assertTrue($start->lessThanOrEqualTo($end));
            });

        $productCoupons = $coupons->where('promo_type', 'product');
        $this->assertSame(10, $productCoupons->count());

        $productAssignments = [];

        foreach ($productCoupons as $coupon) {
            $this->assertGreaterThan(0, $coupon->products->count(), sprintf('Coupon %s should target products.', $coupon->code));

            foreach ($coupon->products as $product) {
                $this->assertArrayNotHasKey(
                    $product->id,
                    $productAssignments,
                    sprintf('Product %d should not be assigned to multiple product coupons.', $product->id)
                );

                $productAssignments[$product->id] = $coupon->id;
            }
        }

        $this->assertSame(
            count($productAssignments),
            DB::table('coupon_product')->count(),
            'Pivot rows should match attached product count.'
        );

        $this->assertSame(
            0,
            Coupon::query()->where('promo_type', '!=', 'product')->whereHas('products')->count(),
            'Non-product coupons must not attach to products.'
        );

        $admin = User::query()->where('email', 'admin@mail.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole(User::ROLE_ADMIN));

        $member = User::query()->where('email', 'user@mail.com')->first();
        $this->assertNotNull($member);
        $this->assertTrue($member->hasRole(User::ROLE_USER));

        $otherUsers = User::query()
            ->whereNotIn('email', ['admin@mail.com', 'user@mail.com'])
            ->get();

        foreach ($otherUsers as $otherUser) {
            $this->assertTrue(
                $otherUser->hasRole(User::ROLE_USER),
                sprintf('User %s should have user role.', $otherUser->email)
            );
        }

        $this->assertGreaterThanOrEqual(52, User::query()->count());

        $tax = DB::table('taxes')->where('name', 'PPN 12%')->first();
        $this->assertNotNull($tax);
        $this->assertEquals(12.00, (float) $tax->rate);

        $banners = Banner::query()->get();
        $this->assertSame(10, $banners->count());

        $expectedCtas = [
            '/collections/new-arrivals',
            '/collections/best-sellers',
            '/collections/signature',
            '/collections/fresh-citrus',
            '/collections/oud-amber',
            '/collections/gifts',
            '/collections/layering',
            '/collections/travel',
            '/collections/laundry',
            '/collections/accessories',
        ];

        $this->assertSame(
            collect($expectedCtas)->sort()->values()->all(),
            $banners->pluck('url')->sort()->values()->all()
        );

        foreach ($banners as $bannerItem) {
            $this->assertNotEmpty($bannerItem->image_description);
            $media = $bannerItem->getMedia(Banner::MEDIA_COLLECTION_NAME);
            $this->assertSame(1, $media->count());
            $signature = $media->first()->getCustomProperty('source_signature');
            $sourceUrl = $media->first()->getCustomProperty('source_url');
            $this->assertNotEmpty($signature);
            $this->assertTrue(
                Str::startsWith($sourceUrl ?? '', 'https://picsum.photos')
                || $sourceUrl === 'local-pool'
                || $signature === 'placeholder'
            );
        }

        $blogs = Blog::with('tags')->get();
        $blogs->each(function (Blog $blog): void {
            $media = $blog->getMedia(Blog::MEDIA_COLLECTION_NAME);
            $this->assertSame(1, $media->count());

            $sourceUrl = $media->first()->getCustomProperty('source_url');
            $signature = $media->first()->getCustomProperty('source_signature');

            if ($signature === 'placeholder' || $sourceUrl === null) {
                $this->assertSame('placeholder', $signature);
            } elseif ($sourceUrl === 'local-pool') {
                $this->assertNotEmpty($signature);
            } else {
                $this->assertTrue(Str::startsWith($sourceUrl, 'https://picsum.photos'));
            }
        });

        $this->assertSame(
            ['Bisnis & Tren Industri', 'Ilmu Wewangian', 'Panduan Parfum', 'Review & Rekomendasi', 'Tips Perawatan & Layering'],
            BlogCategory::query()->pluck('name')->sort()->values()->all()
        );
        $this->assertGreaterThanOrEqual(20, BlogTag::query()->count());

        $this->assertSame(30, $blogs->count());
        $this->assertSame(20, $blogs->where('is_publish', true)->count());
        $this->assertSame(10, $blogs->where('is_publish', false)->count());
        $this->assertGreaterThanOrEqual(5, $blogs->pluck('blog_category_id')->filter()->unique()->count());

        $allowedViews = [0, 25, 50, 75, 100, 125, 150, 175];

        foreach ($blogs as $blog) {
            $wordCount = str_word_count(strip_tags($blog->content));
            $this->assertGreaterThanOrEqual(1000, $wordCount, $blog->slug);
            $this->assertSame((int) ceil($wordCount / 200), $blog->min_read);
            $this->assertContains($blog->views_count, $allowedViews);
            $this->assertNotNull($blog->blog_category_id);
            $this->assertGreaterThanOrEqual(3, $blog->tags->count());
            $this->assertLessThanOrEqual(6, $blog->tags->count());

            $createdAt = CarbonImmutable::parse($blog->created_at);
            $this->assertLessThanOrEqual(90, $createdAt->diffInDays($referenceDate));

            $media = $blog->getFirstMedia(Blog::MEDIA_COLLECTION_NAME);
            $this->assertNotNull($media);
            $this->assertNotEmpty($media->getCustomProperty('source_signature'));
            $this->assertSame(
                1,
                Media::query()
                    ->where('model_type', Blog::class)
                    ->where('model_id', $blog->id)
                    ->count()
            );
        }

        $this->assertSame(
            $blogs->count(),
            Media::query()->where('model_type', Blog::class)->count()
        );

        $completedOrderIds = Order::query()->where('status', Order::STATUS_COMPLETED)->pluck('id');
        $completedItems = DB::table('order_items')->whereIn('order_id', $completedOrderIds)->count();
        $this->assertSame($completedItems, DB::table('product_ratings')->count());
    }

    /**
     * @return array<int, string>
     */
    private function essentialMigrationPaths(): array
    {
        return [
            'database/migrations/0001_01_01_000000_create_users_table.php',
            'database/migrations/2023_09_11_034222_create_banks_table.php',
            'database/migrations/2024_09_22_222114_create_ewallets_table.php',
            'database/migrations/2023_09_11_034340_create_product_categories_table.php',
            'database/migrations/2023_09_11_034348_create_product_brands_table.php',
            'database/migrations/2023_09_11_034514_create_products_table.php',
            'database/migrations/2025_11_18_002420_create_flash_sales_tables.php',
            'database/migrations/2025_11_18_002546_create_flash_sale_products_table.php',
            'database/migrations/2023_09_11_035744_create_user_addresses_table.php',
            'database/migrations/2024_08_06_112850_create_permission_tables.php',
            'database/migrations/2024_08_06_112906_create_media_table.php',
            'database/migrations/2024_09_20_002712_create_blog_categories_table.php',
            'database/migrations/2024_09_20_002753_create_blog_tags_table.php',
            'database/migrations/2024_09_20_002813_create_blogs_table.php',
            'database/migrations/2023_09_11_034114_create_banners_table.php',
            'database/migrations/2024_09_20_002920_create_blog_tag_blog_table.php',
            'database/migrations/2025_09_13_111747_create_coupons_table.php',
            'database/migrations/2025_09_20_000000_create_taxes_table.php',
            'database/migrations/2025_09_12_063034_create_couriers_table.php',
            'database/migrations/2023_09_11_040214_create_orders_table.php',
            'database/migrations/2023_09_11_054547_create_order_items_table.php',
            'database/migrations/2023_09_11_054805_create_invoices_table.php',
            'database/migrations/2023_09_11_055026_create_payments_table.php',
            'database/migrations/2023_09_11_055309_create_payment_banks_table.php',
            'database/migrations/2023_09_11_055319_create_payment_ewallets_table.php',
            'database/migrations/2023_09_11_055833_create_shippings_table.php',
            'database/migrations/2025_07_12_051804_create_product_ratings_table.php',
            'database/migrations/2025_09_13_112211_create_coupon_product_table.php',
        ];
    }

    private function migrateEssentialTables(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropAllTables();
        Schema::enableForeignKeyConstraints();

        collect($this->essentialMigrationPaths())->each(function (string $path): void {
            $migration = require base_path($path);
            $migration->up();
        });
    }
}
