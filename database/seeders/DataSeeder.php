<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Campaign;
use App\Models\CampaignReceipt;
use App\Models\Cart;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Ewallet;
use App\Models\FlashSale;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentBank;
use App\Models\PaymentEwallet;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\Shipping;
use App\Models\Subscriber;
use App\Models\Tax;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserNotification;
use App\Models\Wishlist;
use Database\Factories\FactoryData;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Throwable;

class DataSeeder extends Seeder
{
    use WithoutModelEvents;

    private const PRODUCT_COUNT = 48;

    private const BANNER_COUNT = 6;

    private const BLOG_COUNT = 14;

    private const FLASH_SALE_COUNT = 3;

    private const PERIOD_COUPON_COUNT = 3;

    private const LIMIT_COUPON_COUNT = 2;

    private const PRODUCT_COUPON_COUNT = 4;

    private const CUSTOMER_COUNT = 12;

    private const ORDER_COUNT = 18;

    private const CONTACT_MESSAGE_COUNT = 14;

    private const SUBSCRIBER_COUNT = 24;

    private const CAMPAIGN_COUNT = 3;

    private const PRODUCT_PLACEHOLDER_BASE64 = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAACAAIDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAgP/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCfAAH/2Q==';

    private const PRODUCT_IMAGE_WIDTH = 1200;

    private const PRODUCT_IMAGE_HEIGHT = 1200;

    private const BANNER_IMAGE_WIDTH = 1600;

    private const BANNER_IMAGE_HEIGHT = 600;

    private const BLOG_IMAGE_WIDTH = 1600;

    private const BLOG_IMAGE_HEIGHT = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->shouldSeedMedia()) {
            File::ensureDirectoryExists(storage_path('app/public'));
            File::cleanDirectory(storage_path('app/public'));
        }

        $this->call([
            RolesAndPermissionsSeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            EwalletSeeder::class,
            CourierSeeder::class,
        ]);

        $this->purgeVolatileData();

        $users = $this->seedUsers();
        $this->seedTaxes();

        $products = $this->seedProducts();
        $publishedProducts = $products->where('is_publish', true)->where('stock', '>', 0)->values();

        $this->seedBanners();
        $this->seedBlogs($users);
        $this->seedFlashSales($publishedProducts);

        $coupons = $this->seedCoupons($publishedProducts);

        $customerUsers = $users->reject(fn (User $user): bool => $user->hasRole(User::ROLE_ADMIN))->values();

        $this->seedOrders($customerUsers, $publishedProducts, $coupons);
        $this->seedCartsAndWishlists($customerUsers, $publishedProducts);
        $this->seedNotifications($customerUsers);
        $this->seedContactMessages();

        $subscribers = $this->seedSubscribers();
        $this->seedCampaigns($subscribers);

        $this->call(BannerOrderSeeder::class);
    }

    private function shouldSeedMedia(): bool
    {
        return ! app()->runningUnitTests();
    }

    private function purgeVolatileData(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('media')) {
            DB::table('media')
                ->whereIn('model_type', [
                    Product::class,
                    Banner::class,
                    Blog::class,
                ])
                ->delete();
        }

        $tables = [
            'product_ratings',
            'coupon_usages',
            'coupon_product',
            'flash_sale_products',
            'order_items',
            'payment_banks',
            'payment_ewallets',
            'payments',
            'invoices',
            'shippings',
            'orders',
            'carts',
            'wishlists',
            'user_notifications',
            'campaign_receipts',
            'campaigns',
            'subscribers',
            'contact_messages',
            'blog_tag_blog',
            'blogs',
            'blog_tags',
            'blog_categories',
            'banners',
            'flash_sales',
            'coupons',
            'products',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function productPlaceholderPath(): string
    {
        $directory = database_path('data/seeders');
        $path = $directory.DIRECTORY_SEPARATOR.'product-placeholder.jpg';

        File::ensureDirectoryExists($directory);

        if (! File::exists($path)) {
            File::put($path, base64_decode(self::PRODUCT_PLACEHOLDER_BASE64));
        }

        return $path;
    }

    private function seededImagePath(string $directoryName, string $fileName, string $seed, int $width, int $height): ?string
    {
        if (! $this->shouldSeedMedia()) {
            return null;
        }

        $directory = storage_path('app/seeders/'.$directoryName);
        $path = $directory.DIRECTORY_SEPARATOR.$fileName.'.jpg';

        File::ensureDirectoryExists($directory);

        if (File::exists($path) && File::size($path) > 0) {
            return $path;
        }

        $url = sprintf(
            'https://picsum.photos/seed/%s/%d/%d',
            rawurlencode($seed),
            $width,
            $height
        );

        return $this->downloadSeededImage($url, $path) ? $path : null;
    }

    private function seededProductImagePath(Product $product, int $index, int $imageNumber): ?string
    {
        return $this->seededImagePath(
            'product-images',
            sprintf('%s-%02d', $product->slug, $imageNumber),
            sprintf('%s-%02d-%02d', $product->slug, $index + 1, $imageNumber),
            self::PRODUCT_IMAGE_WIDTH,
            self::PRODUCT_IMAGE_HEIGHT
        );
    }

    private function productImageCount(int $index): int
    {
        return $index % 2 === 0 ? 2 : 3;
    }

    private function seededBannerImagePath(Banner $banner, int $index): ?string
    {
        return $this->seededImagePath(
            'banner-images',
            'banner-'.$banner->id,
            sprintf('banner-%02d-%s', $index + 1, Str::slug((string) $banner->url)),
            self::BANNER_IMAGE_WIDTH,
            self::BANNER_IMAGE_HEIGHT
        );
    }

    private function seededBannerImageVariantPath(Banner $banner, int $index, int $variantNumber): ?string
    {
        return $this->seededImagePath(
            'banner-images',
            sprintf('banner-%d-%02d', $banner->id, $variantNumber),
            sprintf('banner-%02d-%02d-%s', $index + 1, $variantNumber, Str::slug((string) $banner->url)),
            self::BANNER_IMAGE_WIDTH,
            self::BANNER_IMAGE_HEIGHT
        );
    }

    private function seededBlogImagePath(Blog $blog, int $index): ?string
    {
        return $this->seededImagePath(
            'blog-images',
            $blog->slug,
            sprintf('blog-%02d-%s', $index + 1, $blog->slug),
            self::BLOG_IMAGE_WIDTH,
            self::BLOG_IMAGE_HEIGHT
        );
    }

    private function seededBlogImageVariantPath(Blog $blog, int $index, int $variantNumber): ?string
    {
        return $this->seededImagePath(
            'blog-images',
            sprintf('%s-%02d', $blog->slug, $variantNumber),
            sprintf('blog-%02d-%02d-%s', $index + 1, $variantNumber, $blog->slug),
            self::BLOG_IMAGE_WIDTH,
            self::BLOG_IMAGE_HEIGHT
        );
    }

    private function downloadSeededImage(string $url, string $path): bool
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                /** @var Response $response */
                $response = Http::timeout(20)
                    ->withHeaders([
                        'Accept' => 'image/jpeg,image/png,image/*;q=0.9,*/*;q=0.8',
                    ])
                    ->get($url);

                if (! $response->successful()) {
                    continue;
                }

                $body = $response->body();

                if ($body === '') {
                    continue;
                }

                if (@imagecreatefromstring($body) === false) {
                    continue;
                }

                File::put($path, $body);

                return true;
            } catch (Throwable) {
                continue;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, User>
     */
    private function seedUsers(): Collection
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@jaf.test'],
            [
                'name' => 'JAF Admin',
                'password' => 'password',
                'phone' => '081230000001',
                'sex' => 1,
                'birth_date' => '1990-01-15',
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles([User::ROLE_ADMIN]);
        $admin->givePermissionTo(Permission::query()->pluck('name')->all());

        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@jaf.test'],
            [
                'name' => 'JAF Customer',
                'password' => 'password',
                'phone' => '081230000002',
                'sex' => 2,
                'birth_date' => '1994-07-11',
                'email_verified_at' => now(),
            ]
        );

        $customer->syncRoles([User::ROLE_USER]);

        $users = collect([$admin, $customer]);

        for ($index = 1; $index <= self::CUSTOMER_COUNT; $index++) {
            $sex = $index % 2 === 0 ? 2 : 1;

            $user = User::query()->updateOrCreate(
                ['email' => sprintf('customer%02d@jaf.test', $index)],
                [
                    'name' => sprintf('Customer JAF %02d', $index),
                    'password' => 'password',
                    'phone' => sprintf('0812301%05d', $index),
                    'sex' => $sex,
                    'birth_date' => now()->subYears(22 + $index)->format('Y-m-d'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([User::ROLE_USER]);

            UserAddress::query()->updateOrCreate(
                ['user_id' => $user->id],
                FactoryData::address()
            );

            $users->push($user);
        }

        return $users->values();
    }

    private function seedTaxes(): void
    {
        foreach (FactoryData::taxDefinitions() as $tax) {
            Tax::query()->updateOrCreate(
                ['name' => $tax['name']],
                ['rate' => $tax['rate']]
            );
        }
    }

    /**
     * @return Collection<int, Product>
     */
    private function seedProducts(): Collection
    {
        $products = Product::factory()
            ->count(self::PRODUCT_COUNT)
            ->create();

        $products->sortBy('slug')->values()->each(function (Product $product, int $index): void {
            if ($index < 5) {
                $product->forceFill([
                    'is_publish' => false,
                ])->saveQuietly();

                return;
            }

            if ($index < 10) {
                $product->forceFill([
                    'stock' => 0,
                ])->saveQuietly();
            }
        });

        if ($this->shouldSeedMedia()) {
            $placeholderPath = $this->productPlaceholderPath();

            $products->each(function (Product $product, int $index) use ($placeholderPath): void {
                if ($product->hasMedia(Product::MEDIA_COLLECTION_NAME)) {
                    return;
                }

                for ($imageNumber = 1; $imageNumber <= $this->productImageCount($index); $imageNumber++) {
                    $imagePath = $this->seededProductImagePath($product, $index, $imageNumber) ?? $placeholderPath;

                    $product
                        ->addMedia($imagePath)
                        ->preservingOriginal()
                        ->usingFileName(sprintf('%s-%02d.jpg', $product->slug, $imageNumber))
                        ->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
                }
            });
        }

        return Product::query()
            ->with(['brand', 'category'])
            ->get();
    }

    private function seedBanners(): void
    {
        Banner::factory()
            ->count(self::BANNER_COUNT)
            ->create()
            ->each(function (Banner $banner, int $index): void {
                if (! $this->shouldSeedMedia() || $banner->hasMedia(Banner::MEDIA_COLLECTION_NAME)) {
                    return;
                }

                $placeholderPath = $this->productPlaceholderPath();

                for ($variantNumber = 1; $variantNumber <= 2; $variantNumber++) {
                    $imagePath = $this->seededBannerImageVariantPath($banner, $index, $variantNumber)
                        ?? $this->seededBannerImagePath($banner, $index)
                        ?? $placeholderPath;

                    $banner
                        ->addMedia($imagePath)
                        ->preservingOriginal()
                        ->usingFileName(sprintf('banner-%d-%02d.jpg', $banner->id, $variantNumber))
                        ->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);
                }
            });
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function seedBlogs(Collection $users): void
    {
        $categories = collect(FactoryData::blogCategoryNames())
            ->map(function (string $name): BlogCategory {
                return BlogCategory::query()->updateOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name)]
                );
            });

        $tags = collect(FactoryData::blogTagNames())
            ->map(function (string $name): BlogTag {
                return BlogTag::query()->updateOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name)]
                );
            });

        $authors = $users->take(4);

        for ($index = 0; $index < self::BLOG_COUNT; $index++) {
            $blog = Blog::factory()->create([
                'blog_category_id' => $categories[$index % $categories->count()]->id,
                'user_id' => $authors[$index % $authors->count()]->id,
                'is_publish' => $index < 11,
            ]);

            if ($this->shouldSeedMedia()) {
                $placeholderPath = $this->productPlaceholderPath();

                for ($variantNumber = 1; $variantNumber <= 2; $variantNumber++) {
                    $imagePath = $this->seededBlogImageVariantPath($blog, $index, $variantNumber)
                        ?? $this->seededBlogImagePath($blog, $index)
                        ?? $placeholderPath;

                    $blog
                        ->addMedia($imagePath)
                        ->preservingOriginal()
                        ->usingFileName(sprintf('%s-%02d.jpg', $blog->slug, $variantNumber))
                        ->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);
                }
            }

            $blog->tags()->sync(
                $tags->shuffle()
                    ->take(random_int(2, 4))
                    ->pluck('id')
                    ->all()
            );
        }
    }

    /**
     * @param  Collection<int, Product>  $publishedProducts
     */
    private function seedFlashSales(Collection $publishedProducts): void
    {
        FlashSale::factory()
            ->count(self::FLASH_SALE_COUNT)
            ->create()
            ->each(function (FlashSale $flashSale) use ($publishedProducts): void {
                $publishedProducts
                    ->shuffle()
                    ->take(random_int(3, 5))
                    ->each(function (Product $product) use ($flashSale): void {
                        $flashSale->products()->syncWithoutDetaching([
                            $product->id => [
                                'flash_price' => max(10000, (int) round($product->price * fake()->randomFloat(2, 0.68, 0.88))),
                                'stock_flash' => max(3, min($product->stock, random_int(3, 12))),
                                'sold' => random_int(0, 5),
                                'max_qty_per_user' => random_int(1, 3),
                            ],
                        ]);
                    });
            });
    }

    /**
     * @param  Collection<int, Product>  $publishedProducts
     * @return Collection<int, Coupon>
     */
    private function seedCoupons(Collection $publishedProducts): Collection
    {
        $periodCoupons = Coupon::factory()
            ->count(self::PERIOD_COUPON_COUNT)
            ->create([
                'promo_type' => 'period',
            ]);

        $limitCoupons = Coupon::factory()
            ->count(self::LIMIT_COUPON_COUNT)
            ->create([
                'promo_type' => 'limit',
            ]);

        $productCoupons = Coupon::factory()
            ->count(self::PRODUCT_COUPON_COUNT)
            ->create([
                'promo_type' => 'product',
            ]);

        $productCoupons->each(function (Coupon $coupon) use ($publishedProducts): void {
            $eligibleProducts = $publishedProducts
                ->shuffle()
                ->take(random_int(3, 6));

            $coupon->products()->sync($eligibleProducts->pluck('id')->all());
        });

        return $periodCoupons
            ->concat($limitCoupons)
            ->concat($productCoupons)
            ->load('products');
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Coupon>  $coupons
     */
    private function seedOrders(Collection $users, Collection $products, Collection $coupons): void
    {
        $tax = Tax::query()->first();
        $banks = Bank::query()->get();
        $ewallets = Ewallet::query()->get();
        $statuses = [
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_COMPLETED,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_COMPLETED,
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_PROCESSING,
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_COMPLETED,
            Order::STATUS_PENDING,
            Order::STATUS_CANCELLED,
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_COMPLETED,
            Order::STATUS_PROCESSING,
            Order::STATUS_COMPLETED,
        ];

        for ($index = 0; $index < self::ORDER_COUNT; $index++) {
            $status = $statuses[$index % count($statuses)];
            $user = $users[$index % $users->count()];
            $selectedProducts = $products->shuffle()->take(random_int(1, 3))->values();

            $itemPayloads = $selectedProducts->map(function (Product $product): array {
                $quantity = random_int(1, min(3, max(1, $product->stock)));
                $discountInPercent = fake()->randomElement([0, 0, 5, 10, 15]);
                $priceAfterDiscount = (int) round($product->price - (($discountInPercent / 100) * $product->price));

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'discount_in_percent' => $discountInPercent,
                    'price_after_discount' => $priceAfterDiscount,
                    'line_total' => $priceAfterDiscount * $quantity,
                ];
            });

            $subtotal = (int) $itemPayloads->sum('line_total');
            $coupon = $this->pickCouponForProducts($coupons, $selectedProducts, $index);
            $discount = $this->calculateCouponDiscount($coupon, $itemPayloads);
            $shippingCost = random_int(18000, 42000);
            $taxAmount = $tax ? (int) round(max(0, $subtotal - $discount) * ((float) $tax->rate / 100)) : 0;
            $paymentMethod = ['bank', 'ewallet', 'gateway'][$index % 3];
            $gatewayFee = $paymentMethod === 'gateway' ? random_int(2500, 5000) : 0;
            $timeline = $this->orderTimeline($status, $index);
            $weight = (int) $itemPayloads->reduce(
                fn (int $carry, array $item): int => $carry + ($item['product']->weight * $item['quantity']),
                0
            );
            $totalAmount = max(0, $subtotal - $discount + $taxAmount + $shippingCost + $gatewayFee);

            $order = Order::factory()->create([
                'user_id' => $user->id,
                'total_price' => $subtotal,
                'discount' => $discount,
                'discount_name' => $coupon?->name,
                'tax_amount' => $taxAmount,
                'tax_name' => $tax?->name,
                'shipping_cost' => $shippingCost,
                'gateway_fee' => $gatewayFee,
                'gateway_fee_name' => $gatewayFee > 0 ? 'Payment Gateway Fee' : null,
                'note' => fake()->optional(0.6)->randomElement([
                    'Tolong kemas rapi karena untuk hadiah.',
                    'Kirim ke alamat kantor pada jam kerja.',
                    'Mohon bubble wrap tambahan untuk botol kaca.',
                ]),
                'cancel_reason' => $status === Order::STATUS_CANCELLED
                    ? fake()->randomElement([
                        'Pelanggan membatalkan sebelum pembayaran.',
                        'Metode pembayaran diubah setelah checkout.',
                        'Stok batch yang diminta tidak tersedia.',
                    ])
                    : null,
                'status' => $status,
                'created_at' => $timeline['created_at'],
                'updated_at' => $timeline['updated_at'],
                'confirmed_at' => $timeline['confirmed_at'],
                'cancelled_at' => $timeline['cancelled_at'],
                'completed_at' => $timeline['completed_at'],
            ]);

            $itemPayloads->each(function (array $item) use ($order): void {
                /** @var Product $product */
                $product = $item['product'];

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'weight' => $product->weight,
                    'price' => $item['price'],
                    'discount_in_percent' => $item['discount_in_percent'],
                    'price_after_discount' => $item['price_after_discount'],
                    'quantity' => $item['quantity'],
                    'flash_sale_id' => null,
                ]);

                $product->decrement('stock', min($item['quantity'], $product->stock));
            });

            $invoiceStatus = in_array($status, [Order::STATUS_ON_DELIVERY, Order::STATUS_COMPLETED, Order::STATUS_PROCESSING], true)
                ? Invoice::STATUS_PAID
                : Invoice::STATUS_UNPAID;

            $invoice = Invoice::factory()->create([
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'status' => $invoiceStatus,
                'due_date' => $invoiceStatus === Invoice::STATUS_PAID
                    ? $timeline['created_at']->copy()->addDay()
                    : $timeline['created_at']->copy()->addDays(2),
            ]);

            $paymentStatus = match ($status) {
                Order::STATUS_CANCELLED => Payment::STATUS_CANCELLED,
                Order::STATUS_PENDING_PAYMENT => Payment::STATUS_PENDING,
                default => Payment::STATUS_RELEASED,
            };

            $payment = Payment::factory()->create([
                'invoice_id' => $invoice->id,
                'method' => $paymentMethod,
                'amount' => $totalAmount,
                'status' => $paymentStatus,
                'info' => $this->paymentInfo($paymentMethod, $banks, $ewallets),
            ]);

            if ($paymentMethod === Payment::METHOD_BANK) {
                $bank = $banks[$index % $banks->count()];

                PaymentBank::factory()->create([
                    'payment_id' => $payment->id,
                    'name' => $bank->name,
                    'account_name' => $bank->account_name,
                    'account_number' => $bank->account_number,
                ]);
            }

            if ($paymentMethod === Payment::METHOD_EWALLET) {
                $ewallet = $ewallets[$index % $ewallets->count()];

                PaymentEwallet::factory()->create([
                    'payment_id' => $payment->id,
                    'name' => $ewallet->name,
                    'account_name' => $ewallet->account_name,
                    'account_username' => $ewallet->account_username ?? Str::slug($ewallet->name, '').'jaf',
                    'phone' => $ewallet->phone,
                ]);
            }

            $shippingStatus = match ($status) {
                Order::STATUS_ON_DELIVERY, Order::STATUS_COMPLETED => Shipping::STATUS_SHIPPED,
                Order::STATUS_PROCESSING => Shipping::STATUS_PROCESSING,
                default => Shipping::STATUS_PENDING,
            };

            Shipping::factory()->create([
                'order_id' => $order->id,
                'weight' => $weight,
                'status' => $shippingStatus,
            ]);

            if ($coupon !== null) {
                CouponUsage::query()->firstOrCreate([
                    'coupon_id' => $coupon->id,
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        $this->seedRatings();
    }

    private function seedRatings(): void
    {
        OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('status', Order::STATUS_COMPLETED))
            ->get()
            ->shuffle()
            ->take(14)
            ->each(function (OrderItem $item): void {
                ProductRating::query()->updateOrCreate(
                    ['order_item_id' => $item->id],
                    [
                        'rating' => random_int(4, 5),
                        'comment' => fake()->randomElement([
                            'Drydown-nya halus dan tahan cukup lama di kulit.',
                            'Packaging rapi, aroma sesuai ekspektasi, dan cocok dipakai harian.',
                            'Untuk ukuran dan harga segini performanya sangat layak.',
                            'Cocok untuk kantor, tidak terlalu menusuk tapi tetap terasa elegan.',
                        ]),
                        'is_anonymous' => fake()->boolean(20),
                        'is_publish' => true,
                    ]
                );
            });
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     */
    private function seedCartsAndWishlists(Collection $users, Collection $products): void
    {
        $users->take(8)->each(function (User $user) use ($products): void {
            $products->shuffle()->take(2)->each(function (Product $product) use ($user): void {
                Wishlist::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            });

            $products->shuffle()->take(2)->each(function (Product $product) use ($user): void {
                Cart::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => random_int(1, 2),
                    ]
                );
            });
        });
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function seedNotifications(Collection $users): void
    {
        $users->each(function (User $user): void {
            UserNotification::factory()
                ->count(random_int(2, 4))
                ->create([
                    'user_id' => $user->id,
                ]);
        });
    }

    private function seedContactMessages(): void
    {
        ContactMessage::factory()
            ->count(self::CONTACT_MESSAGE_COUNT)
            ->create();
    }

    /**
     * @return Collection<int, Subscriber>
     */
    private function seedSubscribers(): Collection
    {
        return Subscriber::factory()
            ->count(self::SUBSCRIBER_COUNT)
            ->create();
    }

    /**
     * @param  Collection<int, Subscriber>  $subscribers
     */
    private function seedCampaigns(Collection $subscribers): void
    {
        Campaign::factory()
            ->count(self::CAMPAIGN_COUNT)
            ->create()
            ->each(function (Campaign $campaign) use ($subscribers): void {
                $subscribers
                    ->shuffle()
                    ->take(8)
                    ->each(function (Subscriber $subscriber) use ($campaign): void {
                        CampaignReceipt::factory()->create([
                            'campaign_id' => $campaign->id,
                            'subscriber_id' => $subscriber->id,
                        ]);
                    });
            });
    }

    /**
     * @param  Collection<int, Coupon>  $coupons
     * @param  Collection<int, Product>  $selectedProducts
     */
    private function pickCouponForProducts(Collection $coupons, Collection $selectedProducts, int $index): ?Coupon
    {
        if ($coupons->isEmpty() || $index % 2 !== 0) {
            return null;
        }

        $eligibleCoupons = $coupons->filter(function (Coupon $coupon) use ($selectedProducts): bool {
            if ($coupon->promo_type !== 'product') {
                return true;
            }

            $productIds = $selectedProducts->pluck('id');

            return $coupon->products->pluck('id')->intersect($productIds)->isNotEmpty();
        })->values();

        if ($eligibleCoupons->isEmpty()) {
            return null;
        }

        return $eligibleCoupons[$index % $eligibleCoupons->count()];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $itemPayloads
     */
    private function calculateCouponDiscount(?Coupon $coupon, Collection $itemPayloads): int
    {
        if ($coupon === null) {
            return 0;
        }

        $discountBase = (int) $itemPayloads->sum('line_total');

        if ($coupon->promo_type === 'product') {
            $eligibleIds = $coupon->products->pluck('id');

            $discountBase = (int) $itemPayloads
                ->filter(fn (array $item): bool => $eligibleIds->contains($item['product']->id))
                ->sum('line_total');
        }

        if ($discountBase <= 0) {
            return 0;
        }

        if ($coupon->discount_type === 'percentage') {
            return (int) round($discountBase * ((int) $coupon->discount_amount / 100));
        }

        return min($discountBase, (int) $coupon->discount_amount);
    }

    /**
     * @param  Collection<int, Bank>  $banks
     * @param  Collection<int, Ewallet>  $ewallets
     * @return array<string, mixed>
     */
    private function paymentInfo(string $paymentMethod, Collection $banks, Collection $ewallets): array
    {
        if ($paymentMethod === Payment::METHOD_BANK) {
            $bank = $banks->random();

            return [
                'name' => $bank->name,
                'code' => $bank->code,
                'account_name' => $bank->account_name,
                'account_number' => $bank->account_number,
            ];
        }

        if ($paymentMethod === Payment::METHOD_EWALLET) {
            $ewallet = $ewallets->random();

            return [
                'name' => $ewallet->name,
                'account_name' => $ewallet->account_name,
                'account_username' => $ewallet->account_username,
                'phone' => $ewallet->phone,
            ];
        }

        return [
            'provider' => 'midtrans',
            'channel' => fake()->randomElement(['qris', 'gopay', 'bank_transfer']),
            'reference' => 'MID-'.fake()->unique()->numerify('########'),
        ];
    }

    /**
     * @return array{created_at:\Illuminate\Support\Carbon,updated_at:\Illuminate\Support\Carbon,confirmed_at:?\
Illuminate\Support\Carbon,cancelled_at:?\
Illuminate\Support\Carbon,completed_at:?\
Illuminate\Support\Carbon}
     */
    private function orderTimeline(string $status, int $index): array
    {
        $createdAt = now()->subDays(30 - $index)->subHours(random_int(1, 18));
        $confirmedAt = null;
        $cancelledAt = null;
        $completedAt = null;
        $updatedAt = $createdAt;

        if (in_array($status, [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_COMPLETED,
        ], true)) {
            $confirmedAt = $createdAt->copy()->addHours(random_int(1, 10));
            $updatedAt = $confirmedAt;
        }

        if ($status === Order::STATUS_CANCELLED) {
            $cancelledAt = $createdAt->copy()->addHours(random_int(2, 18));
            $updatedAt = $cancelledAt;
        }

        if ($status === Order::STATUS_COMPLETED) {
            $completedAt = $createdAt->copy()->addDays(random_int(2, 8));
            $updatedAt = $completedAt;
        }

        if ($status === Order::STATUS_ON_DELIVERY) {
            $updatedAt = $createdAt->copy()->addDays(random_int(1, 3));
        }

        if ($status === Order::STATUS_PROCESSING) {
            $updatedAt = $createdAt->copy()->addDay();
        }

        return [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'confirmed_at' => $confirmedAt,
            'cancelled_at' => $cancelledAt,
            'completed_at' => $completedAt,
        ];
    }
}
