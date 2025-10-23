<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Throwable;

class DataSeeder extends Seeder
{
    private const PERFUME_TIER_RATES = [
        'designer' => 33000,
        'premium' => 48000,
        'niche' => 85000,
    ];

    private const PERFUME_VOLUME_DISCOUNTS = [
        50 => 1.00,
        75 => 0.98,
        90 => 0.97,
        100 => 0.95,
        150 => 0.92,
        200 => 0.90,
        250 => 0.88,
    ];

    private const LAUNDRY_VOLUME_DISCOUNTS = [
        250 => 1.00,
        500 => 0.95,
        1000 => 0.90,
    ];

    private const PRICE_OVERRIDES = [
        'bleu-de-chanel-edp-100ml' => 2_699_000,
        'dior-sauvage-edp-100ml' => 2_599_000,
        'baccarat-rouge-540-edp-75ml' => 5_999_000,
        'creed-aventus-edp-100ml' => 7_999_000,
    ];

    private const BRAND_NAMES = [
        'Dior',
        'Chanel',
        'Gucci',
        'YSL',
        'Tom Ford',
        'Jo Malone London',
        'MFK',
        'Creed',
        'Byredo',
        'Le Labo',
        'Diptyque',
        'Giorgio Armani',
        'Hermes',
        'Versace',
        'Valentino',
        'Viktor&Rolf',
        'Prada',
        'Lancome',
        'Mugler',
        'Calvin Klein',
        'Burberry',
        'Montblanc',
        'Hugo Boss',
        'Jean Paul Gaultier',
        'Carolina Herrera',
        'Givenchy',
        'Maison Margiela',
        'Kilian Paris',
        'Azzaro',
        'Bvlgari',
        'Downy',
        'Molto',
        'Comfort',
        'So Klin',
        'Attack',
        'Rinso',
        'Softlan',
        'Snuggle',
        'Gain',
        'Tide',
        'JAF Bottles',
    ];

    private const BLOG_CATEGORY_DEFINITIONS = [
        ['name' => 'Panduan Parfum', 'slug' => 'panduan-parfum'],
        ['name' => 'Review & Rekomendasi', 'slug' => 'review-rekomendasi'],
        ['name' => 'Ilmu Wewangian', 'slug' => 'ilmu-wewangian'],
        ['name' => 'Tips Perawatan & Layering', 'slug' => 'tips-perawatan-layering'],
        ['name' => 'Bisnis & Tren Industri', 'slug' => 'bisnis-tren-industri'],
    ];

    private const BLOG_TAG_DEFINITIONS = [
        'citrus',
        'floral',
        'woody',
        'oriental',
        'fougere',
        'chypre',
        'gourmand',
        'musk',
        'amber',
        'oud',
        'vetiver',
        'patchouli',
        'rose',
        'jasmine',
        'edp',
        'edt',
        'extrait',
        'cologne',
        'longevity',
        'sillage',
        'projection',
        'niche',
        'designer',
        'layering',
        'office-safe',
        'date-night',
        'summer',
        'winter',
        'ifra',
        'reformulation',
        'allergen',
        'storage',
        'wardrobe',
        'sampling',
        'decant',
        'outdoor',
        'body-care',
        'aromatherapy',
        'accords',
    ];

    private const BLOG_TITLES = [
        'Panduan Lengkap Memilih Parfum Pertama yang Tepat, dari Tipe Aroma hingga Budget',
        'Perbedaan EDT, EDP, dan Extrait: Cara Memilih Konsentrasi agar Wangi Lebih Tahan Lama',
        'Cara Mengukur Sillage dan Longevity Parfum dengan Benar agar Ekspektasi Tidak Meleset',
        'Layering Parfum untuk Pemula: Dua Belas Resep Aman yang Harum dan Tidak Berlebihan',
        'Mengenal Keluarga Aroma Parfum: Citrus, Floral, Woody, Oriental, sampai Gourmand',
        'Cara Menyimpan Parfum Supaya Tidak Cepat Oksidasi, Hindari Tujuh Kesalahan Umum',
        'Rekomendasi Parfum Aman untuk Iklim Tropis, Tetap Segar Tanpa Mengganggu Sekitar',
        'Niche versus Designer: Perbedaan, Kelebihan, dan Cara Memilih yang Paling Cocok',
        'Mitos dan Fakta Parfum yang Sering Disalahpahami, dari Alkohol sampai Daya Tahan',
        'Reformulasi Parfum: Mengapa Wanginya Berubah dan Apa Saja yang Bisa Kita Lakukan',
        'Belanja Parfum Online dengan Aman, Cek Penjual, Batch Code, dan Kebijakan Retur',
        'Parfum untuk Kantor: Lima Belas Pilihan Wangi Netral dan Tetap Terlihat Profesional',
        'Memilih Parfum Sesuai Cuaca Tropis: Siang Terik, Musim Hujan, dan Acara Malam',
        'Memahami Olfactory Pyramid: Bedanya Top, Heart, dan Base Notes dalam Parfum',
        'Oud, Amber, dan Musk: Tiga Pilar Kehangatan yang Membuat Parfum Terasa Mewah',
        'Parfum Unisex: Mengapa Batas Gender Cair dan Cara Menemukan Wangi Favorit Anda',
        'IFRA, Allergen, dan Keamanan Parfum: Hal Penting yang Perlu Pengguna Ketahui',
        'Membangun Wardrobe Parfum: Lima Botol Serbaguna untuk Semua Aktivitas Harian',
        'Sampling dan Decant dengan Cerdas, Hemat Biaya tanpa Salah Pilih Wangi',
        'Teknik Mencoba Parfum: Blotter versus Kulit, Drydown, dan Membaca Perubahan Aroma',
        'Parfum untuk Olahraga dan Aktivitas Outdoor, Tetap Segar Tanpa Terlalu Menyengat',
        'Menyelaraskan Body Wash, Lotion, dan Parfum: Langkah Praktis Agar Wangi Sinkron',
        'Aromaterapi dan Parfum: Tujuan, Komposisi, serta Kapan Keduanya Tepat Digunakan',
        'Cara Membaca Deskripsi Parfum: Accords, Notes, dan Istilah yang Sering Membingungkan',
        'Kesalahan Saat Memakai Parfum dan Solusinya, dari Jarak Semprot sampai Titik Nadi',
        'Panduan Memberi Hadiah Parfum, Menebak Selera Penerima Tanpa Perlu Spekulasi',
        'Tren Wewangian Dua Ribu Dua Puluh Lima: Fruity Musk hingga Mineral Aquatic',
        'Menemukan Signature Scent, Langkah Praktis agar Wangi Khas Anda Mudah Dikenali',
        'Mengapa Harga Parfum Bisa Mahal, dari Bahan Baku, Riset Formula, hingga Branding',
        'Etika Memakai Parfum di Ruang Publik, Batas Aman Agar Nyaman untuk Semua Orang',
    ];

    private const BLOG_PLACEHOLDER_BASE64 = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAACAAIDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAgP/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCfAAH/2Q==';

    private const BANNER_PLACEHOLDER_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAuEB9p/g9osAAAAASUVORK5CYII=';

    private const DEMO_USER_FIRST_NAMES = [
        'Adi',
        'Siti',
        'Bagus',
        'Rina',
        'Farhan',
        'Intan',
        'Dimas',
        'Nabila',
        'Bima',
        'Alya',
    ];

    private const DEMO_USER_LAST_NAMES = [
        'Pratama',
        'Lestari',
        'Saputra',
        'Wijaya',
        'Utami',
        'Mahardika',
        'Puspita',
        'Permana',
        'Kusuma',
        'Santoso',
    ];

    private const DEMO_ADDRESS_TEMPLATES = [
        [
            'province_id' => 31,
            'city_id' => 3173,
            'district_id' => 3173030,
            'subdistrict_id' => 3173030005,
            'zip' => '12160',
            'street' => 'Jl. Wijaya',
        ],
        [
            'province_id' => 32,
            'city_id' => 3273,
            'district_id' => 3273020,
            'subdistrict_id' => 3273020005,
            'zip' => '40115',
            'street' => 'Jl. Progo',
        ],
        [
            'province_id' => 34,
            'city_id' => 3471,
            'district_id' => 3471030,
            'subdistrict_id' => 3471030007,
            'zip' => '55223',
            'street' => 'Jl. Kaliurang',
        ],
        [
            'province_id' => 35,
            'city_id' => 3578,
            'district_id' => 3578040,
            'subdistrict_id' => 3578040005,
            'zip' => '65145',
            'street' => 'Jl. Soekarno Hatta',
        ],
        [
            'province_id' => 36,
            'city_id' => 3671,
            'district_id' => 3671040,
            'subdistrict_id' => 3671040004,
            'zip' => '15111',
            'street' => 'Jl. Ahmad Yani',
        ],
    ];

    private const BANK_DEFINITIONS = [
        ['name' => 'BCA', 'code' => '014', 'account_name' => 'JAF STORE', 'account_number' => '8888888888'],
        ['name' => 'BNI', 'code' => '009', 'account_name' => 'JAF STORE', 'account_number' => '1234567890'],
        ['name' => 'BRI', 'code' => '002', 'account_name' => 'JAF STORE', 'account_number' => '006601234567890'],
        ['name' => 'Mandiri', 'code' => '008', 'account_name' => 'JAF STORE', 'account_number' => '1200001234567'],
        ['name' => 'Permata', 'code' => '013', 'account_name' => 'JAF STORE', 'account_number' => '708000123456'],
        ['name' => 'CIMB Niaga', 'code' => '022', 'account_name' => 'JAF STORE', 'account_number' => '800012345678'],
        ['name' => 'Danamon', 'code' => '011', 'account_name' => 'JAF STORE', 'account_number' => '7000123456'],
        ['name' => 'BTN', 'code' => '200', 'account_name' => 'JAF STORE', 'account_number' => '1002003004'],
        ['name' => 'OCBC NISP', 'code' => '028', 'account_name' => 'JAF STORE', 'account_number' => '9001234567'],
        ['name' => 'Maybank Indonesia', 'code' => '016', 'account_name' => 'JAF STORE', 'account_number' => '7800123456'],
    ];

    private const EWALLET_DEFINITIONS = [
        ['name' => 'OVO', 'account_name' => 'JAF STORE', 'account_username' => '081234567890', 'phone' => '081234567890'],
        ['name' => 'DANA', 'account_name' => 'JAF STORE', 'account_username' => '081234567891', 'phone' => '081234567891'],
        ['name' => 'GoPay', 'account_name' => 'JAF STORE', 'account_username' => '081234567892', 'phone' => '081234567892'],
        ['name' => 'ShopeePay', 'account_name' => 'JAF STORE', 'account_username' => '081234567893', 'phone' => '081234567893'],
        ['name' => 'LinkAja', 'account_name' => 'JAF STORE', 'account_username' => '081234567894', 'phone' => '081234567894'],
    ];

    private const COURIER_SERVICES = [
        ['courier' => 'LION', 'courier_name' => 'Lion Parcel', 'service' => 'REGPACK', 'service_name' => 'REGPACK', 'etd' => '2-4 HARI', 'shipping_cost' => 20000],
        ['courier' => 'LION', 'courier_name' => 'Lion Parcel', 'service' => 'ONEPACK', 'service_name' => 'ONEPACK', 'etd' => '1-2 HARI', 'shipping_cost' => 32000],
        ['courier' => 'LION', 'courier_name' => 'Lion Parcel', 'service' => 'JTR', 'service_name' => 'JTR', 'etd' => '3-7 HARI', 'shipping_cost' => 42000],
        ['courier' => 'IDE', 'courier_name' => 'ID Express', 'service' => 'REG', 'service_name' => 'Regular', 'etd' => '2-4 HARI', 'shipping_cost' => 19000],
        ['courier' => 'IDE', 'courier_name' => 'ID Express', 'service' => 'ONS', 'service_name' => 'One Day Service', 'etd' => '1-2 HARI', 'shipping_cost' => 31000],
        ['courier' => 'JNT', 'courier_name' => 'J&T Express', 'service' => 'EZ', 'service_name' => 'EZ', 'etd' => '2-4 HARI', 'shipping_cost' => 20500],
        ['courier' => 'JNT', 'courier_name' => 'J&T Express', 'service' => 'JTR', 'service_name' => 'JTR', 'etd' => '3-7 HARI', 'shipping_cost' => 43000],
        ['courier' => 'POS', 'courier_name' => 'POS Indonesia', 'service' => 'KILAT', 'service_name' => 'Paket Kilat Khusus', 'etd' => '3-6 HARI', 'shipping_cost' => 21500],
        ['courier' => 'POS', 'courier_name' => 'POS Indonesia', 'service' => 'EXPRESS', 'service_name' => 'Express Next Day', 'etd' => '1-2 HARI', 'shipping_cost' => 33500],
        ['courier' => 'JNE', 'courier_name' => 'JNE', 'service' => 'REG', 'service_name' => 'Regular', 'etd' => '2-4 HARI', 'shipping_cost' => 21000],
        ['courier' => 'JNE', 'courier_name' => 'JNE', 'service' => 'YES', 'service_name' => 'Yakin Esok Sampai', 'etd' => '1-2 HARI', 'shipping_cost' => 34000],
        ['courier' => 'SICEPAT', 'courier_name' => 'SiCepat Express', 'service' => 'REG', 'service_name' => 'Regular', 'etd' => '2-4 HARI', 'shipping_cost' => 20500],
        ['courier' => 'SICEPAT', 'courier_name' => 'SiCepat Express', 'service' => 'BEST', 'service_name' => 'Besok Sampai Tujuan', 'etd' => '1-2 HARI', 'shipping_cost' => 33000],
        ['courier' => 'SICEPAT', 'courier_name' => 'SiCepat Express', 'service' => 'GOKIL', 'service_name' => 'Cargo', 'etd' => '3-7 HARI', 'shipping_cost' => 39500],
    ];

    private const CANCEL_REASONS = [
        'Customer requested cancellation before payment.',
        'Payment window expired without confirmation.',
        'Inventory adjustment required cancellation.',
        'Address verification failed prior to shipment.',
    ];

    private const RATING_COMMENTS = [
        'Aromanya elegan dan tahan lama.',
        'Kemasan aman, pengiriman tepat waktu.',
        'Wangi lembut cocok dipakai setiap hari.',
        'Sangat puas dengan kualitas produknya.',
        'Harga sebanding dengan kualitas premium.',
    ];

    private ?Collection $perfumeDefinitionsCache = null;

    private ?Collection $laundryDefinitionsCache = null;

    private ?Collection $roomFragranceDefinitionsCache = null;

    private ?Collection $travelDefinitionsCache = null;

    private ?Collection $bottleTemplatesCache = null;

    private Collection $productPayloads;

    private array $blogCategoryMap = [];

    private array $blogTagMap = [];

    private array $blogTagSlugToId = [];

    private ?array $blogImageUrlMap = null;

    private array $blogSlugRegistry = [];

    private ?Collection $publishedProductsCache = null;

    private ?array $userIdsCache = null;

    private array $bankDirectory = [];

    private array $ewalletDirectory = [];

    private array $productCouponPlans = [];

    private CarbonImmutable $referenceDate;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->productPayloads = collect();
        $this->productCouponPlans = [];
        $this->blogCategoryMap = [];
        $this->blogTagMap = [];
        $this->blogTagSlugToId = [];
        $this->blogImageUrlMap = null;
        $this->blogSlugRegistry = [];
        $this->referenceDate = CarbonImmutable::now('Asia/Jakarta')->startOfDay();

        if ($this->shouldSeedRolesAndPermissions()) {
            $this->call(RolesAndPermissionsSeeder::class);
        }

        $this->seedBaseUsers();
        $this->seedDemoUsersWithAddresses();
        $this->call(CourierSeeder::class);

        DB::transaction(function (): void {
            $categories = $this->seedCategories();
            $brands = $this->seedBrands();

            $products = $this->buildProducts($categories, $brands);
            $this->persistProducts($products);
            $this->setPublicationAndStockFlags();
            $this->seedCoupons();
            $this->attachProductCoupons($this->productCouponPlans);
            $this->seedBanks();
            $this->seedEwalletsIfAny();
            $this->seedOrders();
            $this->seedRatingsForCompleted();
            $this->seedTaxes();
            $this->seedBanners();
            $this->seedBlogCategories();
            $this->seedBlogTags();
            $this->seedBlogs();
        });

        $this->attachProductMedia();
    }

    private function seedCategories(): Collection
    {
        $definitions = collect([
            ['name' => 'Parfum'],
            ['name' => 'Parfum Laundry'],
            ['name' => 'Pengharum Ruangan'],
            ['name' => 'Botol Parfum Kosong'],
            ['name' => 'Travel & Decant'],
        ])->map(function (array $definition): array {
            $definition['slug'] = Str::slug($definition['name']);

            return $definition;
        });

        $allowedNames = $definitions->pluck('name')->all();

        ProductCategory::query()
            ->whereNotIn('name', $allowedNames)
            ->delete();

        return $definitions->mapWithKeys(function (array $definition): array {
            $category = ProductCategory::query()->updateOrCreate(
                ['name' => $definition['name']],
                ['slug' => $definition['slug']]
            );

            return [$definition['name'] => $category];
        });
    }

    private function seedBrands(): Collection
    {
        return collect(self::BRAND_NAMES)->mapWithKeys(function (string $name): array {
            $brand = ProductBrand::query()->updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );

            return [$name => $brand];
        });
    }

    private function buildProducts(Collection $categories, Collection $brands): Collection
    {
        $perfumeCategory = $categories->get('Parfum');
        $laundryCategory = $categories->get('Parfum Laundry');
        $bottleCategory = $categories->get('Botol Parfum Kosong');
        $roomCategory = $categories->get('Pengharum Ruangan');
        $travelCategory = $categories->get('Travel & Decant');
        $bottleBrand = $brands->get('JAF Bottles');

        if (! $perfumeCategory || ! $laundryCategory || ! $bottleCategory || ! $roomCategory || ! $travelCategory || ! $bottleBrand) {
            throw new \RuntimeException('DataSeeder requires core categories and JAF Bottles brand to be seeded.');
        }

        $perfumes = $this->buildPerfumeProducts($perfumeCategory, $brands);
        $laundries = $this->buildLaundryProducts($laundryCategory, $brands);
        $bottles = $this->buildBottleProducts($bottleCategory, $bottleBrand);
        $roomFragrances = $this->buildRoomFragranceProducts($roomCategory, $brands);
        $travel = $this->buildTravelProducts($travelCategory, $bottleBrand);

        return collect()
            ->merge($perfumes)
            ->merge($laundries)
            ->merge($bottles)
            ->merge($roomFragrances)
            ->merge($travel);
    }

    private function persistProducts(Collection $products): void
    {
        $this->productPayloads = $products->map(function (array $payload): array {
            /** @var Product $product */
            $product = Product::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                $payload['attributes']
            );

            $payload['model'] = $product;

            return $payload;
        });
    }

    private function setPublicationAndStockFlags(): void
    {
        $sortedSlugs = $this->productPayloads
            ->pluck('slug')
            ->sort()
            ->values();

        $unpublishedSlugs = $sortedSlugs->take(10);
        $outOfStockSlugs = $sortedSlugs->slice(10, 10);

        if ($unpublishedSlugs->isNotEmpty()) {
            Product::query()
                ->whereIn('slug', $unpublishedSlugs)
                ->update(['is_publish' => false]);
        }

        if ($outOfStockSlugs->isNotEmpty()) {
            Product::query()
                ->whereIn('slug', $outOfStockSlugs)
                ->update(['stock' => 0]);
        }

        $this->productPayloads = $this->productPayloads->map(function (array $payload) use ($unpublishedSlugs, $outOfStockSlugs): array {
            /** @var Product $product */
            $product = $payload['model'];

            if ($unpublishedSlugs->contains($payload['slug'])) {
                $product->is_publish = false;
            }

            if ($outOfStockSlugs->contains($payload['slug'])) {
                $product->stock = 0;
            }

            $payload['model'] = $product;

            return $payload;
        });
    }

    private function seedCoupons(): void
    {
        $this->productCouponPlans = [];

        if (! Schema::hasTable('coupons')) {
            info('DataSeeder: coupons table missing, skip coupon seeding.');

            return;
        }

        $currentTimestamp = now('Asia/Jakarta');
        $baseDate = $this->referenceDate;
        $maxEndDate = $baseDate->addDays(30);

        $limitCoupons = collect([
            [
                'code' => 'WELCOME25K',
                'name' => 'Welcome Bonus 25K',
                'description' => 'Voucher sambutan pelanggan baru dengan potongan Rp25.000 untuk transaksi pertama.',
                'discount_type' => 'fixed',
                'discount_amount' => 25_000,
                'limit' => 500,
                'limit_per_user' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'NEWUSER50K',
                'name' => 'New Member 50K',
                'description' => 'Diskon Rp50.000 untuk pelanggan baru yang melengkapi pendaftaran akun.',
                'discount_type' => 'fixed',
                'discount_amount' => 50_000,
                'limit' => 300,
                'limit_per_user' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'BULK10P',
                'name' => 'Bulk Deal 10%',
                'description' => 'Potongan 10% untuk pembelian grosir parfum favorit JAF.',
                'discount_type' => 'percentage',
                'discount_amount' => 10,
                'limit' => 1_000,
                'limit_per_user' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'FLASH5P',
                'name' => 'Flash Sale 5%',
                'description' => 'Flash sale kilat dengan potongan 5% untuk transaksi cepat.',
                'discount_type' => 'percentage',
                'discount_amount' => 5,
                'limit' => 200,
                'limit_per_user' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'SAVE100K',
                'name' => 'Save 100K',
                'description' => 'Voucher Rp100.000 untuk pelanggan setia, saat ini dinonaktifkan.',
                'discount_type' => 'fixed',
                'discount_amount' => 100_000,
                'limit' => 150,
                'limit_per_user' => 1,
                'is_active' => false,
            ],
        ])->map(function (array $coupon) use ($currentTimestamp): array {
            return array_merge($coupon, [
                'promo_type' => 'limit',
                'start_date' => null,
                'end_date' => null,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ]);
        })->all();

        $periodDefinitions = [
            [
                'code' => 'OKT-15P',
                'name' => 'October Aroma 15%',
                'description' => 'Diskon 15% untuk koleksi aroma musiman sepanjang minggu pertama.',
                'discount_type' => 'percentage',
                'discount_amount' => 15,
                'limit' => 600,
                'limit_per_user' => 1,
                'is_active' => true,
                'start_offset' => 1,
                'duration' => 7,
            ],
            [
                'code' => 'PAYDAY-100K',
                'name' => 'Payday Treat 100K',
                'description' => 'Voucher Rp100.000 spesial momen gajian JAF Parfums.',
                'discount_type' => 'fixed',
                'discount_amount' => 100_000,
                'limit' => 500,
                'limit_per_user' => 1,
                'is_active' => true,
                'start_offset' => 3,
                'duration' => 5,
            ],
            [
                'code' => 'MIDWEEK-8P',
                'name' => 'Midweek Refresh 8%',
                'description' => 'Potongan 8% untuk menyegarkan koleksi parfum di tengah minggu.',
                'discount_type' => 'percentage',
                'discount_amount' => 8,
                'limit' => 450,
                'limit_per_user' => 2,
                'is_active' => true,
                'start_offset' => 6,
                'duration' => 5,
            ],
            [
                'code' => 'WEEKEND-20P',
                'name' => 'Weekend Special 20%',
                'description' => 'Promo akhir pekan dengan diskon 20% untuk pilihan parfum unggulan.',
                'discount_type' => 'percentage',
                'discount_amount' => 20,
                'limit' => 300,
                'limit_per_user' => 1,
                'is_active' => true,
                'start_offset' => 9,
                'duration' => 4,
            ],
            [
                'code' => 'NIGHTOWL-60K',
                'name' => 'Night Owl 60K',
                'description' => 'Potongan Rp60.000 untuk pesanan larut malam, saat ini dinonaktifkan.',
                'discount_type' => 'fixed',
                'discount_amount' => 60_000,
                'limit' => 200,
                'limit_per_user' => 1,
                'is_active' => false,
                'start_offset' => 12,
                'duration' => 3,
            ],
            [
                'code' => 'FESTIVE-12P',
                'name' => 'Festive Joy 12%',
                'description' => 'Diskon 12% menyambut perayaan dengan koleksi parfum andalan.',
                'discount_type' => 'percentage',
                'discount_amount' => 12,
                'limit' => 400,
                'limit_per_user' => 3,
                'is_active' => true,
                'start_offset' => 14,
                'duration' => 6,
            ],
            [
                'code' => 'SERENITY-90K',
                'name' => 'Serenity Rebate 90K',
                'description' => 'Voucher Rp90.000 untuk menenangkan hari-hari sibuk Anda.',
                'discount_type' => 'fixed',
                'discount_amount' => 90_000,
                'limit' => 350,
                'limit_per_user' => 2,
                'is_active' => true,
                'start_offset' => 17,
                'duration' => 7,
            ],
            [
                'code' => 'SUNRISE-5P',
                'name' => 'Sunrise Boost 5%',
                'description' => 'Promo 5% untuk pesanan pagi hari, saat ini dinonaktifkan.',
                'discount_type' => 'percentage',
                'discount_amount' => 5,
                'limit' => 500,
                'limit_per_user' => 2,
                'is_active' => false,
                'start_offset' => 19,
                'duration' => 5,
            ],
            [
                'code' => 'VIPSPRING-150K',
                'name' => 'VIP Spring 150K',
                'description' => 'Voucher Rp150.000 khusus anggota VIP selama periode eksklusif.',
                'discount_type' => 'fixed',
                'discount_amount' => 150_000,
                'limit' => 250,
                'limit_per_user' => 1,
                'is_active' => true,
                'start_offset' => 22,
                'duration' => 6,
            ],
            [
                'code' => 'AROMA-18P',
                'name' => 'Aroma Highlight 18%',
                'description' => 'Diskon 18% untuk kurasi parfum pilihan editor JAF, saat ini dinonaktifkan.',
                'discount_type' => 'percentage',
                'discount_amount' => 18,
                'limit' => 280,
                'limit_per_user' => 2,
                'is_active' => false,
                'start_offset' => 24,
                'duration' => 5,
            ],
        ];

        $periodCoupons = collect($periodDefinitions)->map(function (array $coupon) use ($baseDate, $maxEndDate, $currentTimestamp): array {
            $startDate = $baseDate->addDays($coupon['start_offset']);
            $endDate = $startDate->addDays($coupon['duration'] - 1);

            if ($endDate->greaterThan($maxEndDate)) {
                $endDate = $maxEndDate;
            }

            return [
                'code' => $coupon['code'],
                'name' => $coupon['name'],
                'description' => $coupon['description'],
                'promo_type' => 'period',
                'discount_type' => $coupon['discount_type'],
                'discount_amount' => $coupon['discount_amount'],
                'limit' => $coupon['limit'],
                'limit_per_user' => $coupon['limit_per_user'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'is_active' => $coupon['is_active'],
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ];
        })->all();

        $productDefinitions = [
            [
                'code' => 'LAUNDRY-FRESH-10P',
                'name' => 'Laundry Fresh 10%',
                'description' => 'Diskon 10% untuk seri parfum laundry favorit keluarga.',
                'discount_type' => 'percentage',
                'discount_amount' => 10,
                'limit' => 600,
                'limit_per_user' => 2,
                'is_active' => true,
                'start_offset' => 2,
                'duration' => 10,
                'take' => 8,
            ],
            [
                'code' => 'LAUNDRY-DELUXE-150K',
                'name' => 'Laundry Deluxe 150K',
                'description' => 'Potongan Rp150.000 untuk paket deluxe parfum laundry.',
                'discount_type' => 'fixed',
                'discount_amount' => 150_000,
                'limit' => 400,
                'limit_per_user' => 1,
                'is_active' => true,
                'start_offset' => 4,
                'duration' => 11,
                'take' => 6,
            ],
            [
                'code' => 'BOTTLE-CARE-12P',
                'name' => 'Bottle Care 12%',
                'description' => 'Diskon 12% untuk koleksi botol parfum refill premium.',
                'discount_type' => 'percentage',
                'discount_amount' => 12,
                'limit' => 350,
                'limit_per_user' => 3,
                'is_active' => true,
                'start_offset' => 5,
                'duration' => 9,
                'take' => 7,
            ],
            [
                'code' => 'BOTTLE-BONUS-200K',
                'name' => 'Bottle Bonus 200K',
                'description' => 'Voucher Rp200.000 untuk pembelian botol parfum kosong kelas premium.',
                'discount_type' => 'fixed',
                'discount_amount' => 200_000,
                'limit' => 220,
                'limit_per_user' => 1,
                'is_active' => false,
                'start_offset' => 7,
                'duration' => 8,
                'take' => 6,
            ],
            [
                'code' => 'NICHE-ELITE-15P',
                'name' => 'Niche Elite 15%',
                'description' => 'Diskon 15% untuk pilihan parfum niche dan luxury favorit JAF.',
                'discount_type' => 'percentage',
                'discount_amount' => 15,
                'limit' => 280,
                'limit_per_user' => 2,
                'is_active' => true,
                'start_offset' => 8,
                'duration' => 14,
                'take' => 10,
            ],
            [
                'code' => 'NICHE-TREASURE-300K',
                'name' => 'Niche Treasure 300K',
                'description' => 'Voucher Rp300.000 untuk koleksi niche masterpiece pilihan kurator.',
                'discount_type' => 'fixed',
                'discount_amount' => 300_000,
                'limit' => 180,
                'limit_per_user' => 1,
                'is_active' => true,
                'start_offset' => 10,
                'duration' => 10,
                'take' => 8,
            ],
            [
                'code' => 'PARFUM-SIGNATURE-20P',
                'name' => 'Parfum Signature 20%',
                'description' => 'Diskon 20% untuk rangkaian parfum signature JAF Parfums.',
                'discount_type' => 'percentage',
                'discount_amount' => 20,
                'limit' => 500,
                'limit_per_user' => 2,
                'is_active' => true,
                'start_offset' => 12,
                'duration' => 9,
                'take' => 12,
            ],
            [
                'code' => 'PARFUM-FAVORITE-250K',
                'name' => 'Parfum Favorite 250K',
                'description' => 'Voucher Rp250.000 untuk pilihan parfum best-seller, saat ini dinonaktifkan.',
                'discount_type' => 'fixed',
                'discount_amount' => 250_000,
                'limit' => 260,
                'limit_per_user' => 1,
                'is_active' => false,
                'start_offset' => 14,
                'duration' => 8,
                'take' => 9,
            ],
            [
                'code' => 'TRAVEL-SET-12P',
                'name' => 'Travel Set 12%',
                'description' => 'Diskon 12% untuk travel atomizer dan paket decant siap bawa.',
                'discount_type' => 'percentage',
                'discount_amount' => 12,
                'limit' => 300,
                'limit_per_user' => 3,
                'is_active' => true,
                'start_offset' => 16,
                'duration' => 7,
                'take' => 6,
            ],
            [
                'code' => 'MIST-REFRESH-10P',
                'name' => 'Mist Refresh 10%',
                'description' => 'Diskon 10% untuk body mist dan eau de toilette ringan pilihan.',
                'discount_type' => 'percentage',
                'discount_amount' => 10,
                'limit' => 320,
                'limit_per_user' => 2,
                'is_active' => true,
                'start_offset' => 18,
                'duration' => 10,
                'take' => 8,
            ],
        ];

        $this->productCouponPlans = collect($productDefinitions)->mapWithKeys(function (array $coupon): array {
            return [$coupon['code'] => ['take' => $coupon['take']]];
        })->toArray();

        $productCoupons = collect($productDefinitions)->map(function (array $coupon) use ($baseDate, $maxEndDate, $currentTimestamp): array {
            $startDate = $baseDate->addDays($coupon['start_offset']);
            $endDate = $startDate->addDays($coupon['duration'] - 1);

            if ($endDate->greaterThan($maxEndDate)) {
                $endDate = $maxEndDate;
            }

            return [
                'code' => $coupon['code'],
                'name' => $coupon['name'],
                'description' => $coupon['description'],
                'promo_type' => 'product',
                'discount_type' => $coupon['discount_type'],
                'discount_amount' => $coupon['discount_amount'],
                'limit' => $coupon['limit'],
                'limit_per_user' => $coupon['limit_per_user'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'is_active' => $coupon['is_active'],
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ];
        })->all();

        $allCoupons = array_merge($limitCoupons, $periodCoupons, $productCoupons);

        DB::table('coupons')->upsert(
            $allCoupons,
            ['code'],
            [
                'name',
                'description',
                'promo_type',
                'discount_type',
                'discount_amount',
                'limit',
                'limit_per_user',
                'start_date',
                'end_date',
                'is_active',
                'updated_at',
            ]
        );

        info(sprintf(
            'DataSeeder: seeded %d coupons (limit:%d, period:%d, product:%d)',
            count($allCoupons),
            count($limitCoupons),
            count($periodCoupons),
            count($productCoupons)
        ));
    }

    private function attachProductCoupons(array $plans): void
    {
        if (empty($plans)) {
            return;
        }

        $pivotTable = $this->resolveCouponProductTable();

        if (! $pivotTable) {
            info('DataSeeder: coupon pivot table missing, skip product coupon attachments.');

            return;
        }

        $couponCodes = array_keys($plans);

        $couponRecords = DB::table('coupons')
            ->whereIn('code', $couponCodes)
            ->get(['id', 'code'])
            ->keyBy('code');

        if ($couponRecords->isEmpty()) {
            info('DataSeeder: product coupons not found, skip attachments.');

            return;
        }

        $products = Product::query()
            ->without('coupons')
            ->with('brand:id,slug')
            ->where('is_publish', true)
            ->orderBy('slug')
            ->get(['id', 'slug', 'product_brand_id']);

        if ($products->isEmpty()) {
            info('DataSeeder: no published products available for coupon attachments.');

            return;
        }

        $usedProductIds = [];

        foreach ($plans as $code => $configuration) {
            /** @var \Illuminate\Support\Collection|null $couponRecord */
            $couponRecord = $couponRecords->get($code);

            if (! $couponRecord) {
                continue;
            }

            $take = (int) ($configuration['take'] ?? 0);

            if ($take <= 0) {
                continue;
            }

            $selectedProductIds = $this->pickProductsForCode($products, $code, $take, $usedProductIds);

            DB::table($pivotTable)->where('coupon_id', $couponRecord->id)->delete();

            if (empty($selectedProductIds)) {
                info(sprintf('DataSeeder: coupon %s has no eligible products to attach.', $code));

                continue;
            }

            $timestamp = now('Asia/Jakarta');

            $rows = array_map(static fn(int $productId) => [
                'coupon_id' => $couponRecord->id,
                'product_id' => $productId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], $selectedProductIds);

            DB::table($pivotTable)->insert($rows);

            info(sprintf('DataSeeder: coupon %s attached to %d products.', $code, count($rows)));
        }
    }

    private function pickProductsForCode(Collection $products, string $code, int $take, array &$usedProductIds): array
    {
        if ($take <= 0) {
            return [];
        }

        $available = $products->filter(static function (Product $product) use (&$usedProductIds) {
            return ! isset($usedProductIds[$product->id]);
        });

        if ($available->isEmpty()) {
            return [];
        }

        $lowerCode = Str::lower($code);

        $matched = $available->filter(function (Product $product) use ($lowerCode) {
            return $this->productMatchesCouponCode($product, $lowerCode);
        });

        if ($matched->count() < $take) {
            $matched = $matched->merge(
                $available->reject(function (Product $product) use ($lowerCode) {
                    return $this->productMatchesCouponCode($product, $lowerCode);
                })
            );
        }

        $selected = $matched->take($take)->pluck('id')->all();

        foreach ($selected as $productId) {
            $usedProductIds[$productId] = true;
        }

        return $selected;
    }

    private function attachProductMedia(): void
    {
        if (! $this->shouldAttachMedia()) {
            return;
        }

        $this->productPayloads->each(function (array $payload): void {
            /** @var Product $product */
            $product = $payload['model'];

            if ($product->hasMedia(Product::MEDIA_COLLECTION_NAME)) {
                return;
            }

            $candidates = $payload['media_candidates'] ?? [];

            if (empty($candidates)) {
                return;
            }

            $url = $this->pickReachableImageUrl($candidates);

            if (! $url) {
                info(sprintf('DataSeeder: no reachable media for %s', $product->slug));

                return;
            }

            $domain = parse_url($url, PHP_URL_HOST) ?? 'unknown';

            try {
                $product
                    ->addMediaFromUrl($url)
                    ->usingFileName($product->slug . '.jpg')
                    ->withCustomProperties([
                        'source' => $domain,
                        'alt' => $product->name,
                    ])
                    ->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
            } catch (Throwable $exception) {
                info(sprintf('DataSeeder: failed to attach media for %s: %s', $product->slug, $exception->getMessage()));
            }
        });
    }

    private function shouldAttachMedia(): bool
    {
        return (bool) config('seed.attach_media', true);
    }

    private function buildPerfumeProducts(ProductCategory $category, Collection $brands): Collection
    {
        $mediaMap = $this->perfumeMediaMap();

        return $this->perfumeDefinitions()->flatMap(function (array $definition) use ($category, $brands, $mediaMap): Collection {
            $brand = $brands->get($definition['brand']);

            if (! $brand) {
                throw new \RuntimeException(sprintf('Missing brand for perfume: %s', $definition['brand']));
            }

            $baseSlug = $definition['media_key'];
            $mediaCandidates = $mediaMap[$baseSlug] ?? $this->genericPerfumeImages();

            return collect($definition['volumes'])->map(function (int $volume) use ($definition, $category, $brand, $mediaCandidates): array {
                $name = sprintf('%s %dml', $definition['name'], $volume);
                $slug = Str::slug($name);
                $price = $this->calculatePerfumePrice($definition['tier'], $volume, $slug);
                $weight = $this->calculatePerfumeWeight($volume);
                $stock = $this->deterministicStock($slug, 12, 48);

                return [
                    'slug' => $slug,
                    'attributes' => [
                        'product_category_id' => $category->id,
                        'product_brand_id' => $brand->id,
                        'name' => $name,
                        'slug' => $slug,
                        'weight' => $weight,
                        'price' => $price,
                        'stock' => $stock,
                        'description' => $definition['description'],
                        'is_publish' => true,
                        'sex' => $definition['sex'],
                    ],
                    'media_candidates' => $mediaCandidates,
                ];
            });
        });
    }

    private function buildLaundryProducts(ProductCategory $category, Collection $brands): Collection
    {
        $rates = $this->laundryBrandRates();

        return $this->laundryDefinitions()->flatMap(function (array $definition) use ($category, $brands, $rates): Collection {
            $brand = $brands->get($definition['brand']);

            if (! $brand) {
                throw new \RuntimeException(sprintf('Missing laundry brand: %s', $definition['brand']));
            }

            $rate = $rates[$definition['brand']] ?? 130;

            return collect($definition['volumes'])->map(function (int $volume) use ($definition, $category, $brand, $rate): array {
                $name = sprintf('%s %dml', $definition['name'], $volume);
                $slug = Str::slug($name);
                $price = $this->calculateLaundryPrice($rate, $volume, $slug);
                $weight = $this->calculateLaundryWeight($volume);
                $stock = $this->deterministicStock($slug, 20, 30);

                return [
                    'slug' => $slug,
                    'attributes' => [
                        'product_category_id' => $category->id,
                        'product_brand_id' => $brand->id,
                        'name' => $name,
                        'slug' => $slug,
                        'weight' => $weight,
                        'price' => $price,
                        'stock' => $stock,
                        'description' => $definition['description'],
                        'is_publish' => true,
                        'sex' => null,
                    ],
                    'media_candidates' => $definition['media_candidates'],
                ];
            });
        });
    }

    private function buildBottleProducts(ProductCategory $category, ProductBrand $brand): Collection
    {
        return $this->bottleTemplates()->flatMap(function (array $template) use ($category, $brand): Collection {
            return collect($template['capacities'])->map(function (int $capacity) use ($template, $category, $brand): array {
                $name = sprintf('%s %s %dml Bottle', $template['style'], $template['finish'], $capacity);
                $slug = Str::slug($name);
                $price = $this->calculateBottlePrice($template['price_rate'], $template['price_offset'], $capacity, $slug);
                $weight = $this->calculateBottleWeight($template['weight_factor'], $template['weight_offset'], $capacity);
                $stock = $this->deterministicStock($slug, 25, 60);

                return [
                    'slug' => $slug,
                    'attributes' => [
                        'product_category_id' => $category->id,
                        'product_brand_id' => $brand->id,
                        'name' => $name,
                        'slug' => $slug,
                        'weight' => $weight,
                        'price' => $price,
                        'stock' => $stock,
                        'description' => $template['description'],
                        'is_publish' => true,
                        'sex' => null,
                    ],
                    'media_candidates' => $template['media_candidates'],
                ];
            });
        });
    }

    private function buildRoomFragranceProducts(ProductCategory $category, Collection $brands): Collection
    {
        return $this->roomFragranceDefinitions()->map(function (array $definition) use ($category, $brands): array {
            $brand = $brands->get($definition['brand']) ?? $brands->get('Jo Malone London');

            if (! $brand) {
                throw new \RuntimeException(sprintf('Missing room fragrance brand: %s', $definition['brand']));
            }

            $slug = Str::slug($definition['name']);
            $price = $this->calculateRoomFragrancePrice($definition['price_rate'], $definition['volume'], $slug);
            $weight = $this->calculateRoomFragranceWeight($definition['volume']);
            $stock = $this->deterministicStock($slug, 10, 24);

            return [
                'slug' => $slug,
                'attributes' => [
                    'product_category_id' => $category->id,
                    'product_brand_id' => $brand->id,
                    'name' => $definition['name'],
                    'slug' => $slug,
                    'weight' => $weight,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => $definition['description'],
                    'is_publish' => true,
                    'sex' => null,
                ],
                'media_candidates' => $definition['media_candidates'],
            ];
        });
    }

    private function buildTravelProducts(ProductCategory $category, ProductBrand $brand): Collection
    {
        return $this->travelDefinitions()->map(function (array $definition) use ($category, $brand): array {
            $slug = Str::slug($definition['name']);
            $price = $this->calculateTravelPrice($definition['base_rate'], $definition['volume'], $slug);
            $weight = $this->calculateBottleWeight($definition['weight_factor'], $definition['weight_offset'], $definition['volume']);
            $stock = $this->deterministicStock($slug, 18, 36);

            return [
                'slug' => $slug,
                'attributes' => [
                    'product_category_id' => $category->id,
                    'product_brand_id' => $brand->id,
                    'name' => $definition['name'],
                    'slug' => $slug,
                    'weight' => $weight,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => $definition['description'],
                    'is_publish' => true,
                    'sex' => null,
                ],
                'media_candidates' => $definition['media_candidates'],
            ];
        });
    }

    private function calculatePerfumePrice(string $tier, int $volume, string $slug): int
    {
        $rate = self::PERFUME_TIER_RATES[$tier] ?? self::PERFUME_TIER_RATES['designer'];
        $discount = self::PERFUME_VOLUME_DISCOUNTS[$volume] ?? 1.0;

        $priceRaw = $rate * $volume * $discount;
        $priceRaw += $this->deterministicAdjustment($slug);

        $price = (int) ceil($priceRaw / 1000) * 1000;

        return self::PRICE_OVERRIDES[$slug] ?? $price;
    }

    private function calculatePerfumeWeight(int $volume): int
    {
        return (int) round($volume * 3.1 + 180);
    }

    private function calculateLaundryPrice(int $rate, int $volume, string $slug): int
    {
        $discount = self::LAUNDRY_VOLUME_DISCOUNTS[$volume] ?? 1.0;
        $priceRaw = $rate * $volume * $discount;
        $priceRaw += $this->deterministicAdjustment($slug);
        $price = (int) ceil($priceRaw / 1000) * 1000;

        return (int) max(35_000, min(130_000, $price));
    }

    private function calculateLaundryWeight(int $volume): int
    {
        return (int) round($volume * 1.05 + 120);
    }

    private function calculateBottlePrice(int $rate, int $offset, int $capacity, string $slug): int
    {
        $priceRaw = ($capacity * $rate) + $offset + $this->deterministicAdjustment($slug);
        $price = (int) ceil($priceRaw / 1000) * 1000;

        return (int) max(10_000, min(150_000, $price));
    }

    private function calculateBottleWeight(float $factor, int $offset, int $capacity): int
    {
        return (int) round($capacity * $factor + $offset);
    }

    private function calculateRoomFragrancePrice(int $rate, int $volume, string $slug): int
    {
        $priceRaw = $rate * $volume + $this->deterministicAdjustment($slug);
        $price = (int) ceil($priceRaw / 1000) * 1000;

        return (int) max(180_000, min(650_000, $price));
    }

    private function calculateRoomFragranceWeight(int $volume): int
    {
        return (int) round($volume * 1.2 + 220);
    }

    private function calculateTravelPrice(int $rate, int $volume, string $slug): int
    {
        $priceRaw = $rate * $volume + $this->deterministicAdjustment($slug);
        $price = (int) ceil($priceRaw / 1000) * 1000;

        return (int) max(30_000, min(220_000, $price));
    }

    private function deterministicStock(string $slug, int $base, int $range): int
    {
        return $base + (crc32($slug) % $range);
    }

    private function deterministicAdjustment(string $slug): int
    {
        return (int) ((crc32($slug) % 7) * 1000);
    }

    private function pickReachableImageUrl(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            try {
                $head = Http::timeout(10)->head($candidate);

                if ($head->successful() && str_contains((string) $head->header('Content-Type'), 'image')) {
                    return $candidate;
                }

                if ($head->status() === 405 || $head->status() === 403) {
                    $get = Http::timeout(10)->get($candidate);

                    if ($get->successful() && str_contains((string) $get->header('Content-Type'), 'image')) {
                        return $candidate;
                    }
                }
            } catch (Throwable) {
                // Continue to next candidate
            }
        }

        return null;
    }

    private function perfumeDefinitions(): Collection
    {
        if ($this->perfumeDefinitionsCache instanceof Collection) {
            return $this->perfumeDefinitionsCache;
        }

        $definitions = array_merge(
            $this->perfumeDefinitionsPartOne(),
            $this->perfumeDefinitionsPartTwo(),
            $this->perfumeDefinitionsPartThree()
        );

        $this->perfumeDefinitionsCache = collect($definitions);

        return $this->perfumeDefinitionsCache;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function perfumeDefinitionsPartOne(): array
    {
        return [
            [
                'brand' => 'Dior',
                'name' => 'Dior Sauvage EDP',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Fresh bergamot, pepper, and ambroxan.',
                'volumes' => [50, 100, 150],
                'media_key' => 'dior-sauvage-edp',
            ],
            [
                'brand' => 'Dior',
                'name' => 'Dior J\'adore EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Lush floral bouquet with ylang-ylang.',
                'volumes' => [50, 75, 100],
                'media_key' => 'dior-jadore-edp',
            ],
            [
                'brand' => 'Chanel',
                'name' => 'Bleu de Chanel EDP',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Aromatic citrus anchored by cedarwood.',
                'volumes' => [50, 100, 200],
                'media_key' => 'bleu-de-chanel-edp',
            ],
            [
                'brand' => 'Chanel',
                'name' => 'Coco Mademoiselle EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Sparkling orange with patchouli.',
                'volumes' => [50, 75, 100],
                'media_key' => 'coco-mademoiselle-edp',
            ],
            [
                'brand' => 'Chanel',
                'name' => 'Chance Eau Tendre',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Juicy grapefruit and jasmine petals.',
                'volumes' => [50, 75, 150],
                'media_key' => 'chance-eau-tendre',
            ],
            [
                'brand' => 'Gucci',
                'name' => 'Gucci Guilty Pour Homme EDP',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Spicy citrus with patchouli base.',
                'volumes' => [50, 90, 150],
                'media_key' => 'gucci-guilty-pour-homme-edp',
            ],
            [
                'brand' => 'Gucci',
                'name' => 'Gucci Bloom Nettare di Fiori',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Velvety tuberose and jasmine nectar.',
                'volumes' => [50, 75, 100],
                'media_key' => 'gucci-bloom-nettare-di-fiori',
            ],
            [
                'brand' => 'YSL',
                'name' => 'YSL Libre EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Lavender, orange blossom, and musk accord.',
                'volumes' => [50, 90, 150],
                'media_key' => 'ysl-libre-edp',
            ],
            [
                'brand' => 'YSL',
                'name' => 'YSL La Nuit de L\'Homme',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Cardamom, cedar, and coumarin warmth.',
                'volumes' => [50, 100, 150],
                'media_key' => 'ysl-la-nuit-de-lhomme',
            ],
            [
                'brand' => 'Tom Ford',
                'name' => 'Tom Ford Black Orchid Parfum',
                'tier' => 'premium',
                'sex' => 3,
                'description' => 'Dark orchid, truffle, and patchouli.',
                'volumes' => [50, 75, 150],
                'media_key' => 'tom-ford-black-orchid-parfum',
            ],
            [
                'brand' => 'Tom Ford',
                'name' => 'Tom Ford Oud Wood EDP',
                'tier' => 'premium',
                'sex' => 3,
                'description' => 'Smoky oud with cardamom spice.',
                'volumes' => [50, 100, 150],
                'media_key' => 'tom-ford-oud-wood-edp',
            ],
            [
                'brand' => 'Jo Malone London',
                'name' => 'Jo Malone Wood Sage & Sea Salt',
                'tier' => 'premium',
                'sex' => 3,
                'description' => 'Earthy sage and mineral sea breeze.',
                'volumes' => [50, 100, 150],
                'media_key' => 'jo-malone-wood-sage-sea-salt',
            ],
            [
                'brand' => 'Jo Malone London',
                'name' => 'Jo Malone English Pear & Freesia',
                'tier' => 'premium',
                'sex' => 2,
                'description' => 'Pear nectar with soft freesia petals.',
                'volumes' => [50, 100, 150],
                'media_key' => 'jo-malone-english-pear-freesia',
            ],
            [
                'brand' => 'MFK',
                'name' => 'Baccarat Rouge 540 EDP',
                'tier' => 'niche',
                'sex' => 3,
                'description' => 'Jasmine, saffron, and ambergris blend.',
                'volumes' => [50, 75, 150],
                'media_key' => 'baccarat-rouge-540-edp',
            ],
            [
                'brand' => 'MFK',
                'name' => 'MFK Aqua Universalis Forte',
                'tier' => 'niche',
                'sex' => 3,
                'description' => 'Effervescent citrus and clean musk.',
                'volumes' => [50, 100, 150],
                'media_key' => 'mfk-aqua-universalis-forte',
            ],
            [
                'brand' => 'Creed',
                'name' => 'Creed Aventus EDP',
                'tier' => 'niche',
                'sex' => 1,
                'description' => 'Pineapple smoke and birch woods.',
                'volumes' => [50, 100, 150],
                'media_key' => 'creed-aventus-edp',
            ],
            [
                'brand' => 'Creed',
                'name' => 'Creed Green Irish Tweed',
                'tier' => 'niche',
                'sex' => 1,
                'description' => 'Fresh green violet leaf and ambergris.',
                'volumes' => [50, 100, 150],
                'media_key' => 'creed-green-irish-tweed',
            ],
            [
                'brand' => 'Byredo',
                'name' => 'Byredo Gypsy Water',
                'tier' => 'niche',
                'sex' => 3,
                'description' => 'Juniper berries with creamy sandalwood.',
                'volumes' => [50, 100, 200],
                'media_key' => 'byredo-gypsy-water',
            ],
            [
                'brand' => 'Byredo',
                'name' => 'Byredo Bal d\'Afrique',
                'tier' => 'niche',
                'sex' => 3,
                'description' => 'Vetiver, neroli, and African marigold.',
                'volumes' => [50, 100, 200],
                'media_key' => 'byredo-bal-dafrique',
            ],
            [
                'brand' => 'Le Labo',
                'name' => 'Le Labo Santal 33',
                'tier' => 'niche',
                'sex' => 3,
                'description' => 'Creamy sandalwood with leather smoke.',
                'volumes' => [50, 100, 200],
                'media_key' => 'le-labo-santal-33',
            ],
            [
                'brand' => 'Le Labo',
                'name' => 'Le Labo Another 13',
                'tier' => 'niche',
                'sex' => 3,
                'description' => 'Iso E Super and ambergris glow.',
                'volumes' => [50, 100, 150],
                'media_key' => 'le-labo-another-13',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function perfumeDefinitionsPartTwo(): array
    {
        return [
            [
                'brand' => 'Diptyque',
                'name' => 'Diptyque Philosykos EDP',
                'tier' => 'niche',
                'sex' => 3,
                'description' => 'Green fig leaves and milky woods.',
                'volumes' => [50, 75, 150],
                'media_key' => 'diptyque-philosykos-edp',
            ],
            [
                'brand' => 'Diptyque',
                'name' => 'Diptyque Eau Rose EDP',
                'tier' => 'niche',
                'sex' => 2,
                'description' => 'Velvety rose with lychee brightness.',
                'volumes' => [50, 75, 150],
                'media_key' => 'diptyque-eau-rose-edp',
            ],
            [
                'brand' => 'Giorgio Armani',
                'name' => 'Giorgio Armani Acqua di Gio Profumo',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Marine incense with bergamot.',
                'volumes' => [50, 100, 200],
                'media_key' => 'acqua-di-gio-profumo',
            ],
            [
                'brand' => 'Giorgio Armani',
                'name' => 'Giorgio Armani Si EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Blackcurrant nectar and vanilla woods.',
                'volumes' => [50, 100, 150],
                'media_key' => 'giorgio-armani-si-edp',
            ],
            [
                'brand' => 'Hermes',
                'name' => 'Hermes Terre d\'Hermes',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Mineral orange with vetiver strength.',
                'volumes' => [50, 100, 150],
                'media_key' => 'terre-dhermes',
            ],
            [
                'brand' => 'Hermes',
                'name' => 'Hermes Twilly d\'Hermes',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Spicy ginger with tuberose silk.',
                'volumes' => [50, 75, 100],
                'media_key' => 'twilly-dhermes',
            ],
            [
                'brand' => 'Versace',
                'name' => 'Versace Eros EDP',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Fresh mint, tonka, and cedarwood.',
                'volumes' => [50, 100, 150],
                'media_key' => 'versace-eros-edp',
            ],
            [
                'brand' => 'Versace',
                'name' => 'Versace Bright Crystal Absolu',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Bright pomegranate and peony bloom.',
                'volumes' => [50, 75, 150],
                'media_key' => 'versace-bright-crystal-absolu',
            ],
            [
                'brand' => 'Valentino',
                'name' => 'Valentino Uomo Born in Roma',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Violet, vetiver, and mineral facets.',
                'volumes' => [50, 100, 150],
                'media_key' => 'valentino-uomo-born-in-roma',
            ],
            [
                'brand' => 'Valentino',
                'name' => 'Valentino Donna Born in Roma',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Jasmine, vanilla, and cashmeran.',
                'volumes' => [50, 100, 150],
                'media_key' => 'valentino-donna-born-in-roma',
            ],
            [
                'brand' => 'Viktor&Rolf',
                'name' => 'Viktor&Rolf Flowerbomb EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Explosive jasmine and patchouli petals.',
                'volumes' => [50, 100, 150],
                'media_key' => 'viktor-rolf-flowerbomb-edp',
            ],
            [
                'brand' => 'Viktor&Rolf',
                'name' => 'Viktor&Rolf Spicebomb Extreme',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Warm spices with tobacco vanilla.',
                'volumes' => [50, 90, 150],
                'media_key' => 'viktor-rolf-spicebomb-extreme',
            ],
            [
                'brand' => 'Prada',
                'name' => 'Prada Luna Rossa Carbon',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Citrus, lavender, and ambroxan.',
                'volumes' => [50, 100, 150],
                'media_key' => 'prada-luna-rossa-carbon',
            ],
            [
                'brand' => 'Prada',
                'name' => 'Prada Paradoxe EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Neroli bud and amber musk duet.',
                'volumes' => [50, 100, 150],
                'media_key' => 'prada-paradoxe-edp',
            ],
            [
                'brand' => 'Lancome',
                'name' => 'Lancome La Vie Est Belle',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Iris gourmand with praline sweetness.',
                'volumes' => [50, 75, 100],
                'media_key' => 'lancome-la-vie-est-belle',
            ],
            [
                'brand' => 'Lancome',
                'name' => 'Lancome Idole EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Clean rose and pear luminosity.',
                'volumes' => [50, 75, 100],
                'media_key' => 'lancome-idole-edp',
            ],
            [
                'brand' => 'Mugler',
                'name' => 'Mugler Alien Goddess',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Solar jasmine and vanilla infusion.',
                'volumes' => [50, 75, 100],
                'media_key' => 'mugler-alien-goddess',
            ],
            [
                'brand' => 'Mugler',
                'name' => 'Mugler Angel EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Praline patchouli signature sweetness.',
                'volumes' => [50, 75, 100],
                'media_key' => 'mugler-angel-edp',
            ],
            [
                'brand' => 'Calvin Klein',
                'name' => 'Calvin Klein CK One',
                'tier' => 'designer',
                'sex' => 3,
                'description' => 'Citrus green tea freshness.',
                'volumes' => [50, 100, 200],
                'media_key' => 'calvin-klein-ck-one',
            ],
            [
                'brand' => 'Calvin Klein',
                'name' => 'Calvin Klein Eternity for Men EDP',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Aromatic sage and sandalwood.',
                'volumes' => [50, 100, 150],
                'media_key' => 'calvin-klein-eternity-men-edp',
            ],
            [
                'brand' => 'Burberry',
                'name' => 'Burberry Her EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Juicy berries with creamy musk.',
                'volumes' => [50, 75, 100],
                'media_key' => 'burberry-her-edp',
            ],
            [
                'brand' => 'Burberry',
                'name' => 'Burberry Mr. Burberry Indigo',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Citrus woods with herbal accents.',
                'volumes' => [50, 100, 150],
                'media_key' => 'mr-burberry-indigo',
            ],
            [
                'brand' => 'Montblanc',
                'name' => 'Montblanc Explorer EDP',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Bergamot, vetiver, and patchouli.',
                'volumes' => [50, 100, 150],
                'media_key' => 'montblanc-explorer-edp',
            ],
            [
                'brand' => 'Montblanc',
                'name' => 'Montblanc Legend Spirit',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Citrus aquatic with white woods.',
                'volumes' => [50, 100, 150],
                'media_key' => 'montblanc-legend-spirit',
            ],
            [
                'brand' => 'Hugo Boss',
                'name' => 'Hugo Boss Boss Bottled Parfum',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Apple, sage, and leather woods.',
                'volumes' => [50, 100, 150],
                'media_key' => 'boss-bottled-parfum',
            ],
            [
                'brand' => 'Hugo Boss',
                'name' => 'Hugo Boss The Scent For Her',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Peach blossom with cocoa base.',
                'volumes' => [50, 75, 100],
                'media_key' => 'boss-the-scent-for-her',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function perfumeDefinitionsPartThree(): array
    {
        return [
            [
                'brand' => 'Jean Paul Gaultier',
                'name' => 'Jean Paul Gaultier Le Male Le Parfum',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Cardamom lavender with woods.',
                'volumes' => [50, 100, 150],
                'media_key' => 'le-male-le-parfum',
            ],
            [
                'brand' => 'Jean Paul Gaultier',
                'name' => 'Jean Paul Gaultier Scandal EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Honeyed tuberose and jasmine.',
                'volumes' => [50, 75, 100],
                'media_key' => 'scandal-edp',
            ],
            [
                'brand' => 'Carolina Herrera',
                'name' => 'Carolina Herrera Good Girl EDP',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Cocoa, tuberose, and tonka.',
                'volumes' => [50, 75, 100],
                'media_key' => 'good-girl-edp',
            ],
            [
                'brand' => 'Carolina Herrera',
                'name' => 'Carolina Herrera 212 VIP Men',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Lime caviar and gin accord.',
                'volumes' => [50, 100, 150],
                'media_key' => 'carolina-herrera-212-vip-men',
            ],
            [
                'brand' => 'Givenchy',
                'name' => 'Givenchy Gentleman Reserve Privee',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Iris whiskey with chestnut warmth.',
                'volumes' => [50, 100, 150],
                'media_key' => 'givenchy-gentleman-reserve-privee',
            ],
            [
                'brand' => 'Givenchy',
                'name' => 'Givenchy L\'Interdit Rouge',
                'tier' => 'designer',
                'sex' => 2,
                'description' => 'Tuberose with spicy blood orange.',
                'volumes' => [50, 75, 100],
                'media_key' => 'givenchy-linterdit-rouge',
            ],
            [
                'brand' => 'Maison Margiela',
                'name' => 'Maison Margiela Replica Jazz Club',
                'tier' => 'premium',
                'sex' => 1,
                'description' => 'Rum, tobacco, and vanilla smoke.',
                'volumes' => [50, 100, 150],
                'media_key' => 'replica-jazz-club',
            ],
            [
                'brand' => 'Maison Margiela',
                'name' => 'Maison Margiela Replica Lazy Sunday Morning',
                'tier' => 'premium',
                'sex' => 2,
                'description' => 'Clean musk and lily of the valley.',
                'volumes' => [50, 100, 150],
                'media_key' => 'replica-lazy-sunday-morning',
            ],
            [
                'brand' => 'Kilian Paris',
                'name' => 'Kilian Paris Love Don\'t Be Shy',
                'tier' => 'premium',
                'sex' => 2,
                'description' => 'Orange blossom and marshmallow glow.',
                'volumes' => [50, 75, 100],
                'media_key' => 'kilian-love-dont-be-shy',
            ],
            [
                'brand' => 'Kilian Paris',
                'name' => 'Kilian Paris Black Phantom',
                'tier' => 'premium',
                'sex' => 3,
                'description' => 'Coffee rum with dark chocolate.',
                'volumes' => [50, 75, 100],
                'media_key' => 'kilian-black-phantom',
            ],
            [
                'brand' => 'Azzaro',
                'name' => 'Azzaro Wanted by Night',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Spicy cinnamon and tobacco accord.',
                'volumes' => [50, 100, 150],
                'media_key' => 'azzaro-wanted-by-night',
            ],
            [
                'brand' => 'Azzaro',
                'name' => 'Azzaro Chrome Extreme',
                'tier' => 'designer',
                'sex' => 1,
                'description' => 'Aquatic citrus with cedar drydown.',
                'volumes' => [50, 100, 150],
                'media_key' => 'azzaro-chrome-extreme',
            ],
            [
                'brand' => 'Bvlgari',
                'name' => 'Bvlgari Man in Black',
                'tier' => 'premium',
                'sex' => 1,
                'description' => 'Amber rum and leather warmth.',
                'volumes' => [50, 100, 150],
                'media_key' => 'bvlgari-man-in-black',
            ],
            [
                'brand' => 'Bvlgari',
                'name' => 'Bvlgari Omnia Crystalline',
                'tier' => 'premium',
                'sex' => 2,
                'description' => 'Lotus, pear, and bamboo clarity.',
                'volumes' => [50, 75, 100],
                'media_key' => 'bvlgari-omnia-crystalline',
            ],
        ];
    }

    private function laundryDefinitions(): Collection
    {
        if ($this->laundryDefinitionsCache instanceof Collection) {
            return $this->laundryDefinitionsCache;
        }

        $definitions = [
            [
                'brand' => 'Downy',
                'name' => 'Downy Premium Parfum Mystique Softener',
                'description' => 'Rich parfum-inspired softness for long-lasting freshness.',
                'volumes' => [250, 500, 1000],
                'media_seed' => 'laundry-downy-premium-parfum-mystique',
            ],
            [
                'brand' => 'Downy',
                'name' => 'Downy Sunrise Fresh Softener',
                'description' => 'Bright morning florals to uplift laundry day.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-downy-sunrise-fresh',
            ],
            [
                'brand' => 'Molto',
                'name' => 'Molto Ultra Pure Gentle Softener',
                'description' => 'Hypoallergenic softness for sensitive fabrics.',
                'volumes' => [250, 500, 1000],
                'media_seed' => 'laundry-molto-ultra-pure',
            ],
            [
                'brand' => 'Molto',
                'name' => 'Molto Korean Strawberry Softener',
                'description' => 'Sweet berry aroma inspired by Seoul boutiques.',
                'volumes' => [250, 500],
                'media_seed' => 'laundry-molto-korean-strawberry',
            ],
            [
                'brand' => 'Comfort',
                'name' => 'Comfort Luxury Nature Freshener',
                'description' => 'Botanical extracts delivering enduring freshness.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-comfort-luxury-nature',
            ],
            [
                'brand' => 'So Klin',
                'name' => 'So Klin Royale Hot Summer Softener',
                'description' => 'Tropical fruits and florals with odor guard.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-soklin-royale-hot-summer',
            ],
            [
                'brand' => 'Attack',
                'name' => 'Attack Jaz1 Super Solusi Detergent',
                'description' => 'Concentrated detergent with anti-bacterial shield.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-attack-jaz1-super',
            ],
            [
                'brand' => 'Rinso',
                'name' => 'Rinso Matic Perfume Essence Liquid',
                'description' => 'High-efficiency wash with perfume microcapsules.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-rinso-matic-perfume-essence',
            ],
            [
                'brand' => 'Softlan',
                'name' => 'Softlan Floral Bouquet Softener',
                'description' => 'Soft touch finish with romantic florals.',
                'volumes' => [250, 500],
                'media_seed' => 'laundry-softlan-floral-bouquet',
            ],
            [
                'brand' => 'Snuggle',
                'name' => 'Snuggle Blue Sparkle Liquid',
                'description' => 'Classic cozy scent with anti-static care.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-snuggle-blue-sparkle',
            ],
            [
                'brand' => 'Gain',
                'name' => 'Gain Moonlight Breeze Detergent',
                'description' => 'Bold nighttime florals with freshness lock.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-gain-moonlight-breeze',
            ],
            [
                'brand' => 'Tide',
                'name' => 'Tide Downy April Fresh Liquid',
                'description' => 'Powerful clean fused with comforting Downy notes.',
                'volumes' => [500, 1000],
                'media_seed' => 'laundry-tide-downy-april-fresh',
            ],
        ];

        $this->laundryDefinitionsCache = collect($definitions)->map(function (array $definition) {
            $seed = $definition['media_seed'] ?? 'laundry-' . Str::slug($definition['name']);
            $definition['media_candidates'] = [$this->picsumSeedUrl($seed, 1200, 1200)];
            unset($definition['media_seed']);

            return $definition;
        });

        return $this->laundryDefinitionsCache;
    }

    private function roomFragranceDefinitions(): Collection
    {
        if ($this->roomFragranceDefinitionsCache instanceof Collection) {
            return $this->roomFragranceDefinitionsCache;
        }

        $definitions = [
            [
                'brand' => 'Jo Malone London',
                'name' => 'Jo Malone Lime Basil & Mandarin Diffuser 200ml',
                'volume' => 200,
                'price_rate' => 2200,
                'description' => 'Herbal citrus diffuser that refreshes open living spaces.',
                'media_seed' => 'room-jo-malone-lime-basil-mandarin-diffuser',
            ],
            [
                'brand' => 'Diptyque',
                'name' => 'Diptyque Baies Candle 190ml',
                'volume' => 190,
                'price_rate' => 2500,
                'description' => 'Iconic berry and rose candle for cozy evenings.',
                'media_seed' => 'room-diptyque-baies-candle',
            ],
            [
                'brand' => 'Maison Margiela',
                'name' => 'Replica Lazy Sunday Room Spray 250ml',
                'volume' => 250,
                'price_rate' => 2100,
                'description' => 'Crisp cotton and musk spray for linen and rooms.',
                'media_seed' => 'room-replica-lazy-sunday-room-spray',
            ],
            [
                'brand' => 'Byredo',
                'name' => 'Byredo Cotton Poplin Candle 240ml',
                'volume' => 240,
                'price_rate' => 2450,
                'description' => 'Blue chamomile and linen accord for gentle ambience.',
                'media_seed' => 'room-byredo-cotton-poplin-candle',
            ],
            [
                'brand' => 'Jo Malone London',
                'name' => 'Jo Malone Peony & Blush Suede Room Spray 175ml',
                'volume' => 175,
                'price_rate' => 2050,
                'description' => 'Flirtatious peony mist to brighten intimate rooms.',
                'media_seed' => 'room-jo-malone-peony-blush-spray',
            ],
            [
                'brand' => 'Diptyque',
                'name' => 'Diptyque Roses Diffuser 200ml',
                'volume' => 200,
                'price_rate' => 2300,
                'description' => 'Elegant rose petals diffused through fine reeds.',
                'media_seed' => 'room-diptyque-roses-diffuser',
            ],
        ];

        $this->roomFragranceDefinitionsCache = collect($definitions)->map(function (array $definition) {
            $seed = $definition['media_seed'] ?? 'room-' . Str::slug($definition['name']);
            $definition['media_candidates'] = [$this->picsumSeedUrl($seed, 1600, 1000)];
            unset($definition['media_seed']);

            return $definition;
        });

        return $this->roomFragranceDefinitionsCache;
    }

    private function travelDefinitions(): Collection
    {
        if ($this->travelDefinitionsCache instanceof Collection) {
            return $this->travelDefinitionsCache;
        }

        $definitions = [
            [
                'name' => 'JAF Bottles Travel Atomizer Set 10ml',
                'volume' => 10,
                'base_rate' => 1800,
                'weight_factor' => 2.2,
                'weight_offset' => 60,
                'description' => 'Aluminium refillable atomizer with leakproof core.',
                'media_seed' => 'travel-atomizer-set-10ml',
            ],
            [
                'name' => 'JAF Bottles Glass Rollerball Duo 15ml',
                'volume' => 15,
                'base_rate' => 1900,
                'weight_factor' => 2.6,
                'weight_offset' => 70,
                'description' => 'Thick glass rollerball duo ideal for oil-based decants.',
                'media_seed' => 'travel-rollerball-duo-15ml',
            ],
            [
                'name' => 'JAF Bottles Twist Refill Atomizer 20ml',
                'volume' => 20,
                'base_rate' => 2100,
                'weight_factor' => 2.4,
                'weight_offset' => 80,
                'description' => 'Twist-to-open decant bottle with internal glass vial.',
                'media_seed' => 'travel-twist-refill-atomizer-20ml',
            ],
            [
                'name' => 'JAF Bottles Magnetic Travel Spray 50ml',
                'volume' => 50,
                'base_rate' => 2300,
                'weight_factor' => 2.9,
                'weight_offset' => 110,
                'description' => 'Magnetic cap travel spray with internal glass canister.',
                'media_seed' => 'travel-magnetic-spray-50ml',
            ],
            [
                'name' => 'JAF Bottles Pocket Splitter 30ml',
                'volume' => 30,
                'base_rate' => 2000,
                'weight_factor' => 2.5,
                'weight_offset' => 90,
                'description' => 'Compact splitter bottle with dual-channel funnel.',
                'media_seed' => 'travel-pocket-splitter-30ml',
            ],
            [
                'name' => 'JAF Bottles Glass Pipette Decant 25ml',
                'volume' => 25,
                'base_rate' => 1950,
                'weight_factor' => 2.7,
                'weight_offset' => 85,
                'description' => 'Dropper-style bottle for precise extrait decanting.',
                'media_seed' => 'travel-glass-pipette-decant-25ml',
            ],
        ];

        $this->travelDefinitionsCache = collect($definitions)->map(function (array $definition) {
            $seed = $definition['media_seed'] ?? 'travel-' . Str::slug($definition['name']);
            $definition['media_candidates'] = [$this->picsumSeedUrl($seed, 1200, 1200)];
            unset($definition['media_seed']);

            return $definition;
        });

        return $this->travelDefinitionsCache;
    }

    private function bottleTemplates(): Collection
    {
        if ($this->bottleTemplatesCache instanceof Collection) {
            return $this->bottleTemplatesCache;
        }

        $templates = [
            [
                'style' => 'Glass Square',
                'finish' => 'Silver Spray',
                'capacities' => [10, 30, 50],
                'price_rate' => 900,
                'price_offset' => 4_000,
                'weight_factor' => 2.6,
                'weight_offset' => 80,
                'description' => 'Thick glass square bottle with polished shoulders.',
                'media_seed' => 'bottle-glass-square-silver',
            ],
            [
                'style' => 'Frosted Cylinder',
                'finish' => 'Matte Black Atomizer',
                'capacities' => [10, 50, 100],
                'price_rate' => 950,
                'price_offset' => 4_500,
                'weight_factor' => 2.8,
                'weight_offset' => 90,
                'description' => 'Frosted cylindrical bottle with minimalist atomizer.',
                'media_seed' => 'bottle-frosted-cylinder-matte-black',
            ],
            [
                'style' => 'Magnetic Prestige',
                'finish' => 'Gunmetal Cap',
                'capacities' => [50, 100, 150],
                'price_rate' => 980,
                'price_offset' => 6_000,
                'weight_factor' => 3.0,
                'weight_offset' => 130,
                'description' => 'Premium heavy base bottle with magnetic enclosure.',
                'media_seed' => 'bottle-magnetic-prestige-gunmetal',
            ],
            [
                'style' => 'Crystal Prism',
                'finish' => 'Rose Gold Pump',
                'capacities' => [30, 50, 100],
                'price_rate' => 920,
                'price_offset' => 5_000,
                'weight_factor' => 2.9,
                'weight_offset' => 120,
                'description' => 'Faceted crystal bottle catching light from every angle.',
                'media_seed' => 'bottle-crystal-prism-rose-gold',
            ],
            [
                'style' => 'Minimalist Matte',
                'finish' => 'Soft Touch Spray',
                'capacities' => [10, 30, 100],
                'price_rate' => 860,
                'price_offset' => 4_200,
                'weight_factor' => 2.5,
                'weight_offset' => 85,
                'description' => 'Matte lacquered bottle with soft touch finish.',
                'media_seed' => 'bottle-minimalist-matte-soft-touch',
            ],
            [
                'style' => 'Amber Apothecary',
                'finish' => 'Natural Cork',
                'capacities' => [50, 100, 150],
                'price_rate' => 780,
                'price_offset' => 3_800,
                'weight_factor' => 2.7,
                'weight_offset' => 110,
                'description' => 'Amber-toned apothecary bottle ideal for oil blends.',
                'media_seed' => 'bottle-amber-apothecary-cork',
            ],
            [
                'style' => 'Travel Atomizer',
                'finish' => 'Anodized Aluminum',
                'capacities' => [5, 10, 15],
                'price_rate' => 1_050,
                'price_offset' => 3_500,
                'weight_factor' => 2.2,
                'weight_offset' => 60,
                'description' => 'Portable anodized shell with internal glass vial.',
                'media_seed' => 'bottle-travel-atomizer-anodized',
            ],
            [
                'style' => 'Rollerball Duo',
                'finish' => 'Steel Ball',
                'capacities' => [10, 15, 30],
                'price_rate' => 820,
                'price_offset' => 3_600,
                'weight_factor' => 2.3,
                'weight_offset' => 70,
                'description' => 'Dual rollerball glass set for oil perfumery.',
                'media_seed' => 'bottle-rollerball-duo-steel',
            ],
            [
                'style' => 'Vintage Bulb',
                'finish' => 'Atomizer Bulb',
                'capacities' => [50, 100, 150],
                'price_rate' => 970,
                'price_offset' => 5_500,
                'weight_factor' => 3.1,
                'weight_offset' => 140,
                'description' => 'Classic bulb atomizer evokes vintage vanity glamour.',
                'media_seed' => 'bottle-vintage-bulb-atomizer',
            ],
            [
                'style' => 'Wooden Collar',
                'finish' => 'Magnetic Closure',
                'capacities' => [50, 100, 120],
                'price_rate' => 940,
                'price_offset' => 5_200,
                'weight_factor' => 2.95,
                'weight_offset' => 125,
                'description' => 'Modern squared bottle with oak-grain collar.',
                'media_seed' => 'bottle-wooden-collar-magnetic',
            ],
            [
                'style' => 'Facet Deco',
                'finish' => 'Chrome Spray',
                'capacities' => [30, 50, 100],
                'price_rate' => 910,
                'price_offset' => 4_700,
                'weight_factor' => 2.8,
                'weight_offset' => 115,
                'description' => 'Art-deco glass with mirrored chrome spray hardware.',
                'media_seed' => 'bottle-facet-deco-chrome',
            ],
            [
                'style' => 'Portable Splitter',
                'finish' => 'Leakproof Twist',
                'capacities' => [5, 10, 20],
                'price_rate' => 880,
                'price_offset' => 3_400,
                'weight_factor' => 2.1,
                'weight_offset' => 55,
                'description' => 'Leakproof splitter bottle for sharing decants.',
                'media_seed' => 'bottle-portable-splitter-twist',
            ],
            [
                'style' => 'Clear PET Refill',
                'finish' => 'Screw Cap',
                'capacities' => [100, 200, 250],
                'price_rate' => 520,
                'price_offset' => 2_400,
                'weight_factor' => 1.8,
                'weight_offset' => 75,
                'description' => 'Lightweight PET refill bottle for studios and labs.',
                'media_seed' => 'bottle-clear-pet-refill',
            ],
            [
                'style' => 'Glass Dropper',
                'finish' => 'Pipette Cap',
                'capacities' => [15, 30, 50],
                'price_rate' => 840,
                'price_offset' => 3_700,
                'weight_factor' => 2.4,
                'weight_offset' => 82,
                'description' => 'Precision pipette bottle perfect for extrait blends.',
                'media_seed' => 'bottle-glass-dropper-pipette',
            ],
            [
                'style' => 'Crystal Column',
                'finish' => 'Gold Spray',
                'capacities' => [30, 50, 100],
                'price_rate' => 960,
                'price_offset' => 5_100,
                'weight_factor' => 2.85,
                'weight_offset' => 118,
                'description' => 'Columnar crystal bottle crowned with luxe gold sprayer.',
                'media_seed' => 'bottle-crystal-column-gold',
            ],
        ];

        $this->bottleTemplatesCache = collect($templates)->map(function (array $template) {
            $seed = $template['media_seed'] ?? 'bottle-' . Str::slug($template['style'] . ' ' . $template['finish']);
            $template['media_candidates'] = [$this->picsumSeedUrl($seed, 1200, 1200)];
            unset($template['media_seed']);

            return $template;
        });

        return $this->bottleTemplatesCache;
    }

    private function perfumeMediaMap(): array
    {
        $map = [];

        foreach ($this->perfumeDefinitions() as $definition) {
            $key = $definition['media_key'];
            $map[$key] = $this->picsumSeedSet('perfume-' . $key, 3, 1200, 1200);
        }

        return $map;
    }

    private function laundryBrandRates(): array
    {
        return [
            'Downy' => 135,
            'Molto' => 120,
            'Comfort' => 125,
            'So Klin' => 118,
            'Attack' => 130,
            'Rinso' => 122,
            'Softlan' => 112,
            'Snuggle' => 128,
            'Gain' => 132,
            'Tide' => 138,
        ];
    }

    protected function seedBanks(): void
    {
        $this->bankDirectory = [];

        if (! Schema::hasTable('banks')) {
            info('DataSeeder: banks table missing, skip bank seeding.');
            $this->bankDirectory = array_map(fn(array $bank) => $bank + ['id' => null], self::BANK_DEFINITIONS);

            return;
        }

        $timestamp = $this->referenceDate->toMutable();

        $rows = array_map(
            fn(array $bank) => array_merge($bank, [
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]),
            self::BANK_DEFINITIONS
        );

        try {
            DB::table('banks')->upsert(
                $rows,
                ['code'],
                ['name', 'account_name', 'account_number', 'updated_at']
            );
        } catch (Throwable) {
            DB::table('banks')->upsert(
                $rows,
                ['account_number'],
                ['name', 'account_name', 'code', 'updated_at']
            );
        }

        $this->bankDirectory = DB::table('banks')
            ->whereIn('code', array_column(self::BANK_DEFINITIONS, 'code'))
            ->orderBy('code')
            ->get(['id', 'name', 'code', 'account_name', 'account_number'])
            ->map(fn($row) => (array) $row)
            ->values()
            ->all();

        info(sprintf('DataSeeder: seeded %d banks.', count($this->bankDirectory)));
    }

    protected function seedEwalletsIfAny(): void
    {
        $this->ewalletDirectory = [];

        if (! Schema::hasTable('ewallets')) {
            info('DataSeeder: ewallets table missing, using fallback data.');
            $this->ewalletDirectory = array_map(fn(array $wallet) => $wallet + ['id' => null], self::EWALLET_DEFINITIONS);

            return;
        }

        $timestamp = $this->referenceDate->toMutable();

        $rows = array_map(
            fn(array $wallet) => array_merge($wallet, [
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]),
            self::EWALLET_DEFINITIONS
        );

        foreach ($rows as $wallet) {
            $existing = DB::table('ewallets')
                ->where('name', $wallet['name'])
                ->where('account_username', $wallet['account_username'])
                ->first();

            if ($existing) {
                DB::table('ewallets')
                    ->where('id', $existing->id)
                    ->update([
                        'account_name' => $wallet['account_name'],
                        'phone' => $wallet['phone'],
                        'updated_at' => $wallet['updated_at'],
                    ]);
            } else {
                DB::table('ewallets')->insert($wallet);
            }
        }

        $this->ewalletDirectory = DB::table('ewallets')
            ->whereIn('name', array_column(self::EWALLET_DEFINITIONS, 'name'))
            ->orderBy('name')
            ->get(['id', 'name', 'account_name', 'account_username', 'phone'])
            ->map(fn($row) => (array) $row)
            ->values()
            ->all();

        info(sprintf('DataSeeder: seeded %d ewallets.', count($this->ewalletDirectory)));
    }

    protected function userIds(): array
    {
        if ($this->userIdsCache !== null) {
            return $this->userIdsCache;
        }

        if (! Schema::hasTable('users')) {
            $this->userIdsCache = [];

            return $this->userIdsCache;
        }

        $this->userIdsCache = DB::table('users')
            ->orderBy('id')
            ->limit(50)
            ->pluck('id')
            ->all();

        return $this->userIdsCache;
    }

    protected function publishedProducts(): Collection
    {
        if ($this->publishedProductsCache instanceof Collection) {
            return $this->publishedProductsCache;
        }

        $this->publishedProductsCache = Product::query()
            ->where('is_publish', true)
            ->orderBy('slug')
            ->get(['id', 'name', 'slug', 'price', 'weight', 'product_brand_id']);

        return $this->publishedProductsCache;
    }

    protected function statusDistribution(): array
    {
        return array_merge(
            array_fill(0, 10, Order::STATUS_PENDING_PAYMENT),
            array_fill(0, 25, Order::STATUS_PENDING),
            array_fill(0, 30, Order::STATUS_PROCESSING),
            array_fill(0, 25, Order::STATUS_ON_DELIVERY),
            array_fill(0, 50, Order::STATUS_COMPLETED),
            array_fill(0, 10, Order::STATUS_CANCELLED)
        );
    }

    protected function uniqueOrderNo(int $index, \DateTimeInterface $date): string
    {
        return sprintf('ORD-%s-%06d', $date->format('Ymd'), $index + 1);
    }

    protected function uniqueInvoiceNo(int $index, \DateTimeInterface $date): string
    {
        return sprintf('INV-%s-%06d', $date->format('Ymd'), $index + 1);
    }

    protected function trackingNo(int $index, \DateTimeInterface $date): string
    {
        return sprintf('TRK%s%06d', $date->format('Ymd'), $index + 1);
    }

    protected function resolveDiscountForOrder(int $index, int $subtotal): array
    {
        if ($subtotal <= 0) {
            return [0, null];
        }

        $discount = 0;
        $label = null;

        if ($index % 12 === 0) {
            $discount = (int) round($subtotal * 0.05);
            $label = 'Loyalty 5%';
        } elseif ($index % 15 === 8) {
            $discount = 30_000;
            $label = 'Voucher 30K';
        } elseif ($index % 20 === 5) {
            $discount = (int) round($subtotal * 0.08);
            $label = 'Seasonal 8%';
        }

        $discount = min($discount, $subtotal);

        if ($discount <= 0) {
            return [0, null];
        }

        return [$discount, $label];
    }

    protected function computeMoney(array $items, int $discount, string $taxName = 'PPN 12%', float $taxRate = 12.00): array
    {
        $subtotal = array_reduce($items, function (int $carry, array $item): int {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $taxableBase = max($subtotal - $discount, 0);
        $taxAmount = (int) round($taxableBase * ($taxRate / 100));

        return [
            'subtotal' => $subtotal,
            'taxable_base' => $taxableBase,
            'tax_amount' => $taxAmount,
        ];
    }

    protected function pickCourierFor(int $index): array
    {
        return self::COURIER_SERVICES[$index % count(self::COURIER_SERVICES)];
    }

    protected function shippingAddressSnapshot(int $userId): array
    {
        if (! Schema::hasTable('user_addresses')) {
            return $this->defaultShippingSnapshot($userId);
        }

        $address = DB::table('user_addresses')
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();

        if (! $address) {
            return $this->defaultShippingSnapshot($userId);
        }

        return [
            'name' => $address->name,
            'phone' => $this->normalizePhone($address->phone),
            'province' => $this->regionNameFromId('province', $address->province_id),
            'city' => $this->regionNameFromId('city', $address->city_id),
            'district' => $this->regionNameFromId('district', $address->district_id),
            'subdistrict' => $this->regionNameFromId('subdistrict', $address->subdistrict_id),
            'zip_code' => $address->zip_code ?? '68121',
            'address' => $address->address,
        ];
    }

    private function defaultShippingSnapshot(int $userId): array
    {
        return [
            'name' => sprintf('Customer %d', $userId),
            'phone' => $this->normalizePhone('0812300000' . str_pad((string) ($userId % 100), 2, '0', STR_PAD_LEFT)),
            'province' => 'Jawa Timur',
            'city' => 'Jember',
            'district' => 'Sumbersari',
            'subdistrict' => 'Sumbersari',
            'zip_code' => '68121',
            'address' => 'Jl. Bangka VII No. 12 Jember',
        ];
    }

    private function regionNameFromId(string $segment, ?int $id): string
    {
        $defaults = [
            'province' => 'Jawa Timur',
            'city' => 'Jember',
            'district' => 'Sumbersari',
            'subdistrict' => 'Sumbersari',
        ];

        $base = $defaults[$segment] ?? 'Indonesia';

        if (! $id) {
            return $base;
        }

        return sprintf('%s #%d', $base, $id);
    }

    private function normalizePhone(?string $phone): string
    {
        if (! $phone) {
            return '6281234567890';
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (Str::startsWith($digits, '62')) {
            return $digits;
        }

        if (Str::startsWith($digits, '0')) {
            return '62' . ltrim($digits, '0');
        }

        return '62' . $digits;
    }

    private function cancelReasonForOrder(int $index): string
    {
        return self::CANCEL_REASONS[$index % count(self::CANCEL_REASONS)];
    }

    protected function shippingStatusForOrder(string $orderStatus): string
    {
        return match ($orderStatus) {
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_PENDING,
            Order::STATUS_CANCELLED => 'pending',
            Order::STATUS_PROCESSING => 'processing',
            Order::STATUS_ON_DELIVERY,
            Order::STATUS_COMPLETED => 'shipped',
            default => 'pending',
        };
    }

    protected function upsertOrderGraph(array $payload): void
    {
        $orderData = $payload['order'];
        $orderNote = $orderData['note'];
        $orderCreatedAt = $orderData['created_at'];
        $orderUpdatedAt = $orderData['updated_at'];

        $orderAttributes = $orderData;
        unset($orderAttributes['created_at'], $orderAttributes['updated_at']);

        $existingOrder = DB::table('orders')->where('note', $orderNote)->first();

        if ($existingOrder) {
            DB::table('orders')
                ->where('id', $existingOrder->id)
                ->update(array_merge($orderAttributes, ['updated_at' => $orderUpdatedAt]));

            $orderId = $existingOrder->id;
        } else {
            $orderId = DB::table('orders')->insertGetId(array_merge(
                $orderAttributes,
                [
                    'note' => $orderNote,
                    'created_at' => $orderCreatedAt,
                    'updated_at' => $orderUpdatedAt,
                ]
            ));
        }

        DB::table('order_items')->where('order_id', $orderId)->delete();

        $items = array_map(function (array $item) use ($orderId, $orderUpdatedAt): array {
            return array_merge($item, [
                'order_id' => $orderId,
                'created_at' => $orderUpdatedAt,
                'updated_at' => $orderUpdatedAt,
            ]);
        }, $payload['items']);

        if (! empty($items)) {
            DB::table('order_items')->insert($items);
        }

        $invoice = $payload['invoice'];
        $existingInvoice = DB::table('invoices')->where('number', $invoice['number'])->first();

        if ($existingInvoice) {
            DB::table('invoices')
                ->where('id', $existingInvoice->id)
                ->update([
                    'order_id' => $orderId,
                    'amount' => $invoice['amount'],
                    'status' => $invoice['status'],
                    'due_date' => $invoice['due_date'],
                    'updated_at' => $orderUpdatedAt,
                ]);

            $invoiceId = $existingInvoice->id;
        } else {
            $invoiceId = DB::table('invoices')->insertGetId([
                'order_id' => $orderId,
                'number' => $invoice['number'],
                'amount' => $invoice['amount'],
                'status' => $invoice['status'],
                'due_date' => $invoice['due_date'],
                'created_at' => $orderCreatedAt,
                'updated_at' => $orderUpdatedAt,
            ]);
        }

        $payment = $payload['payment'];
        $paymentInfo = json_encode($payment['info'], JSON_UNESCAPED_UNICODE);
        $existingPayment = DB::table('payments')->where('invoice_id', $invoiceId)->first();

        if ($existingPayment) {
            DB::table('payments')
                ->where('id', $existingPayment->id)
                ->update([
                    'method' => $payment['method'],
                    'info' => $paymentInfo,
                    'amount' => $payment['amount'],
                    'status' => $payment['status'],
                    'updated_at' => $orderUpdatedAt,
                ]);

            $paymentId = $existingPayment->id;
        } else {
            $paymentId = DB::table('payments')->insertGetId([
                'invoice_id' => $invoiceId,
                'method' => $payment['method'],
                'info' => $paymentInfo,
                'amount' => $payment['amount'],
                'status' => $payment['status'],
                'created_at' => $orderCreatedAt,
                'updated_at' => $orderUpdatedAt,
            ]);
        }

        if ($payment['method'] === 'bank') {
            DB::table('payment_ewallets')->where('payment_id', $paymentId)->delete();

            $existingBank = DB::table('payment_banks')->where('payment_id', $paymentId)->first();

            $bankPayload = [
                'name' => $payment['meta']['name'],
                'account_name' => $payment['meta']['account_name'],
                'account_number' => $payment['meta']['account_number'],
                'updated_at' => $orderUpdatedAt,
            ];

            if ($existingBank) {
                DB::table('payment_banks')
                    ->where('id', $existingBank->id)
                    ->update($bankPayload);
            } else {
                DB::table('payment_banks')->insert(array_merge(
                    ['payment_id' => $paymentId],
                    $bankPayload,
                    ['created_at' => $orderCreatedAt]
                ));
            }
        } else {
            DB::table('payment_banks')->where('payment_id', $paymentId)->delete();

            $existingWallet = DB::table('payment_ewallets')->where('payment_id', $paymentId)->first();

            $walletPayload = [
                'name' => $payment['meta']['name'],
                'account_name' => $payment['meta']['account_name'],
                'account_username' => $payment['meta']['account_username'],
                'phone' => $payment['meta']['phone'],
                'updated_at' => $orderUpdatedAt,
            ];

            if ($existingWallet) {
                DB::table('payment_ewallets')
                    ->where('id', $existingWallet->id)
                    ->update($walletPayload);
            } else {
                DB::table('payment_ewallets')->insert(array_merge(
                    ['payment_id' => $paymentId],
                    $walletPayload,
                    ['created_at' => $orderCreatedAt]
                ));
            }
        }

        $shipping = $payload['shipping'];
        $shippingAddress = is_array($shipping['address'])
            ? json_encode($shipping['address'], JSON_UNESCAPED_UNICODE)
            : (string) $shipping['address'];

        $existingShipping = DB::table('shippings')->where('order_id', $orderId)->first();

        $shippingPayload = [
            'address' => $shippingAddress,
            'courier' => $shipping['courier'],
            'courier_name' => $shipping['courier_name'],
            'service' => $shipping['service'],
            'service_name' => $shipping['service_name'],
            'etd' => $shipping['etd'],
            'weight' => $shipping['weight'],
            'tracking_number' => $shipping['tracking_number'],
            'status' => $shipping['status'],
            'updated_at' => $orderUpdatedAt,
        ];

        if ($existingShipping) {
            DB::table('shippings')
                ->where('id', $existingShipping->id)
                ->update($shippingPayload);
        } else {
            DB::table('shippings')->insert(array_merge(
                ['order_id' => $orderId],
                $shippingPayload,
                ['created_at' => $orderCreatedAt]
            ));
        }
    }

    protected function seedOrders(): void
    {
        if (! Schema::hasTable('orders')) {
            info('DataSeeder: orders table missing, skip order seeding.');

            return;
        }

        if (
            ! Schema::hasTable('order_items')
            || ! Schema::hasTable('invoices')
            || ! Schema::hasTable('payments')
            || ! Schema::hasTable('shippings')
            || ! Schema::hasTable('payment_banks')
            || ! Schema::hasTable('payment_ewallets')
        ) {
            info('DataSeeder: dependent order tables missing, skip order seeding.');

            return;
        }

        $userIds = $this->userIds();
        $products = $this->publishedProducts();

        if (empty($userIds) || $products->isEmpty()) {
            info('DataSeeder: insufficient users or products for order seeding.');

            return;
        }

        $statuses = $this->statusDistribution();
        $now = $this->referenceDate->toMutable();

        foreach ($statuses as $index => $status) {
            $createdAt = $this->referenceDate
                ->subDays($index % 30)
                ->setTime(9 + ($index % 5), 15)
                ->toMutable();

            $orderNumber = $this->uniqueOrderNo($index, $createdAt);
            $invoiceNumber = $this->uniqueInvoiceNo($index, $createdAt);
            $trackingNumber = in_array($status, [Order::STATUS_ON_DELIVERY, Order::STATUS_COMPLETED], true)
                ? $this->trackingNo($index, $createdAt)
                : null;

            $userId = $userIds[$index % count($userIds)];

            $itemCount = ($index % 5) + 1;
            $items = [];
            $subtotal = 0;
            $weightTotal = 0;

            for ($k = 0; $k < $itemCount; $k++) {
                $product = $products[($index * 3 + $k) % $products->count()];
                $quantity = (($index + $k) % 3) + 1;
                $lineTotal = $product->price * $quantity;
                $subtotal += $lineTotal;
                $weightTotal += $product->weight * $quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'weight' => $product->weight,
                    'price' => $product->price,
                    'discount_in_percent' => 0,
                    'price_after_discount' => $product->price,
                    'quantity' => $quantity,
                ];
            }

            [$discount, $discountName] = $this->resolveDiscountForOrder($index, $subtotal);

            $money = $this->computeMoney($items, $discount);

            $courier = $this->pickCourierFor($index);
            $shippingCost = $courier['shipping_cost'];
            $totalPrice = $money['taxable_base'] + $money['tax_amount'] + $shippingCost;

            $confirmedAt = in_array($status, [Order::STATUS_PROCESSING, Order::STATUS_ON_DELIVERY, Order::STATUS_COMPLETED], true)
                ? $createdAt->copy()->addHours(8)
                : null;
            $completedAt = $status === Order::STATUS_COMPLETED ? $createdAt->copy()->addHours(8)->addDays(2) : null;
            $cancelledAt = $status === Order::STATUS_CANCELLED ? $createdAt->copy()->addDay() : null;

            $invoiceStatus = in_array($status, [Order::STATUS_PROCESSING, Order::STATUS_ON_DELIVERY, Order::STATUS_COMPLETED], true)
                ? 'paid'
                : 'unpaid';

            $paymentStatus = match ($status) {
                Order::STATUS_PROCESSING,
                Order::STATUS_ON_DELIVERY,
                Order::STATUS_COMPLETED => 'realeased',
                Order::STATUS_CANCELLED => 'cancelled',
                default => 'pending',
            };

            $paymentAmount = match ($paymentStatus) {
                'realeased' => $totalPrice,
                'pending' => max(0, (int) round($totalPrice * 0.5)),
                'cancelled' => 0,
                default => $totalPrice,
            };

            $invoiceDueDate = $createdAt->copy()->addDays(7)->setTime(17, 0);

            $useEwallet = $index % 3 === 1 && ! empty($this->ewalletDirectory);
            $paymentMethod = $useEwallet ? 'ewallet' : 'bank';

            $bankMeta = $this->bankDirectory[$index % max(count($this->bankDirectory), 1)] ?? (self::BANK_DEFINITIONS[$index % count(self::BANK_DEFINITIONS)] + ['id' => null]);
            $walletMeta = $this->ewalletDirectory[$index % max(count($this->ewalletDirectory), 1)] ?? (self::EWALLET_DEFINITIONS[$index % count(self::EWALLET_DEFINITIONS)] + ['id' => null]);

            $paymentInfo = $paymentMethod === 'bank'
                ? [
                    'channel' => 'bank_transfer',
                    'name' => $bankMeta['name'],
                    'code' => $bankMeta['code'] ?? null,
                    'account_number' => $bankMeta['account_number'],
                    'account_name' => $bankMeta['account_name'],
                ]
                : [
                    'channel' => 'ewallet',
                    'name' => $walletMeta['name'],
                    'account_username' => $walletMeta['account_username'],
                    'phone' => $walletMeta['phone'],
                    'account_name' => $walletMeta['account_name'],
                ];

            $paymentMeta = $paymentMethod === 'bank'
                ? [
                    'name' => $bankMeta['name'],
                    'account_name' => $bankMeta['account_name'],
                    'account_number' => $bankMeta['account_number'],
                ]
                : [
                    'name' => $walletMeta['name'],
                    'account_name' => $walletMeta['account_name'],
                    'account_username' => $walletMeta['account_username'],
                    'phone' => $walletMeta['phone'],
                ];

            $shippingSnapshot = $this->shippingAddressSnapshot($userId);

            $this->upsertOrderGraph([
                'order' => [
                    'note' => 'Order No: ' . $orderNumber,
                    'user_id' => $userId,
                    'total_price' => $totalPrice,
                    'discount' => $discount,
                    'discount_name' => $discountName,
                    'tax_amount' => $money['tax_amount'],
                    'tax_name' => 'PPN 12%',
                    'shipping_cost' => $shippingCost,
                    'cancel_reason' => $status === Order::STATUS_CANCELLED ? $this->cancelReasonForOrder($index) : null,
                    'status' => $status,
                    'confirmed_at' => $confirmedAt,
                    'cancelled_at' => $cancelledAt,
                    'completed_at' => $completedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $now,
                ],
                'items' => array_map(function (array $item) use ($now): array {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;

                    return $item;
                }, $items),
                'invoice' => [
                    'number' => $invoiceNumber,
                    'amount' => $totalPrice,
                    'status' => $invoiceStatus,
                    'due_date' => $invoiceDueDate,
                ],
                'payment' => [
                    'method' => $paymentMethod,
                    'status' => $paymentStatus,
                    'amount' => $paymentAmount,
                    'info' => $paymentInfo,
                    'meta' => $paymentMeta,
                ],
                'shipping' => [
                    'address' => $shippingSnapshot,
                    'courier' => $courier['courier'],
                    'courier_name' => $courier['courier_name'],
                    'service' => $courier['service'],
                    'service_name' => $courier['service_name'],
                    'etd' => $courier['etd'],
                    'weight' => $weightTotal,
                    'tracking_number' => $trackingNumber,
                    'status' => $this->shippingStatusForOrder($status),
                ],
            ]);
        }

        info(sprintf('DataSeeder: seeded %d orders with related data.', count($statuses)));
    }

    protected function seedRatingsForCompleted(): void
    {
        if (! Schema::hasTable('product_ratings') || ! Schema::hasTable('order_items')) {
            info('DataSeeder: ratings table missing, skip rating seeding.');

            return;
        }

        $completedOrderIds = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->pluck('id');

        if ($completedOrderIds->isEmpty()) {
            return;
        }

        $items = DB::table('order_items')
            ->whereIn('order_id', $completedOrderIds)
            ->orderBy('id')
            ->get(['id']);

        if ($items->isEmpty()) {
            return;
        }

        $timestamp = $this->referenceDate->toMutable();

        foreach ($items as $index => $item) {
            $ratingValue = 3 + ($index % 3);
            $comment = self::RATING_COMMENTS[$index % count(self::RATING_COMMENTS)];

            if ($index % 4 === 0) {
                $comment = null;
            }

            DB::table('product_ratings')->updateOrInsert(
                ['order_item_id' => $item->id],
                [
                    'rating' => $ratingValue,
                    'comment' => $comment,
                    'is_anonymous' => $index % 3 === 2,
                    'is_publish' => true,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );
        }

        info(sprintf('DataSeeder: seeded %d product ratings.', $items->count()));
    }

    protected function seedTaxes(): void
    {
        if (! Schema::hasTable('taxes')) {
            info('DataSeeder: taxes table missing, skip tax seeding.');

            return;
        }

        $now = now('Asia/Jakarta')->toDateTimeString();

        $existing = DB::table('taxes')->where('name', 'PPN 12%')->first();

        if ($existing) {
            DB::table('taxes')
                ->where('id', $existing->id)
                ->update([
                    'rate' => 12.00,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('taxes')->insert([
                'name' => 'PPN 12%',
                'rate' => 12.00,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        info('DataSeeder: ensured tax PPN 12% exists.');
    }

    protected function seedBanners(): void
    {
        if (! Schema::hasTable('banners')) {
            info('DataSeeder: banners table missing, skip banner seeding.');

            return;
        }

        $definitions = $this->bannerDefinitions();
        $stats = [
            'url_success' => 0,
            'placeholder' => 0,
            'skipped' => 0,
        ];

        foreach ($definitions as $index => $definition) {
            $timestamp = $this->referenceDate
                ->subDays($index % 30)
                ->setTime(9 + ($index % 3), 0, 0)
                ->toDateTimeString();

            $existing = DB::table('banners')->where('url', $definition['cta'])->first();

            if ($existing) {
                DB::table('banners')
                    ->where('id', $existing->id)
                    ->update([
                        'image_description' => $definition['desc'],
                        'updated_at' => $timestamp,
                    ]);

                $bannerId = $existing->id;
            } else {
                $bannerId = DB::table('banners')->insertGetId([
                    'image_description' => $definition['desc'],
                    'url' => $definition['cta'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            /** @var Banner|null $banner */
            $banner = Banner::query()->find($bannerId);

            if (! $banner) {
                continue;
            }

            $result = $this->attachBannerImage($banner, $definition['img'], $definition['desc']);
            $stats[$result] = ($stats[$result] ?? 0) + 1;
        }

        info(sprintf(
            'DataSeeder: seeded %d banners (media via URL: %d, placeholder: %d, skipped: %d).',
            count($definitions),
            $stats['url_success'] ?? 0,
            $stats['placeholder'] ?? 0,
            $stats['skipped'] ?? 0
        ));
    }

    protected function shouldSeedRolesAndPermissions(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && DB::table('roles')->count() === 0
            && DB::table('permissions')->count() === 0;
    }

    protected function seedBaseUsers(): void
    {
        if (
            ! Schema::hasTable('users')
            || ! Schema::hasTable('roles')
        ) {
            return;
        }

        $timestamp = $this->referenceDate->toMutable();

        /** @var User $admin */
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        $admin->assignRole(User::ROLE_ADMIN);

        if (Schema::hasTable('permissions')) {
            try {
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            } catch (\Throwable $e) {
                // ignore cache refresh failure
            }

            $allPermissions = Permission::query()->pluck('name')->all();

            if (! empty($allPermissions)) {
                $admin->syncPermissions($allPermissions);
            }
        }

        /** @var User $user */
        $user = User::query()->updateOrCreate(
            ['email' => 'user@mail.com'],
            [
                'name' => 'User',
                'password' => 'password',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        $user->assignRole(User::ROLE_USER);

        User::query()
            ->whereNotIn('id', [$admin->id, $user->id])
            ->get()
            ->each(function (User $genericUser): void {
                if (! $genericUser->hasRole(User::ROLE_ADMIN)) {
                    $genericUser->assignRole(User::ROLE_USER);
                }
            });
    }

    protected function seedDemoUsersWithAddresses(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $dataset = $this->demoUserDataset();

        if (empty($dataset)) {
            return;
        }

        $now = now('Asia/Jakarta');
        $timestamp = $now->toDateTimeString();

        info(sprintf('DataSeeder: preparing %d demo users.', count($dataset)));

        $emails = array_column($dataset, 'email');

        $existingUsers = User::query()
            ->whereIn('email', $emails)
            ->get()
            ->keyBy('email');

        $newUsers = [];

        foreach ($dataset as $record) {
            if (isset($existingUsers[$record['email']])) {
                $existingUsers[$record['email']]->forceFill([
                    'name' => $record['name'],
                    'phone' => $record['phone'],
                    'updated_at' => $now,
                ])->save();

                continue;
            }

            $newUsers[] = [
                'name' => $record['name'],
                'email' => $record['email'],
                'password' => Hash::make('password'),
                'phone' => $record['phone'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (! empty($newUsers)) {
            DB::table('users')->insert($newUsers);

            $existingUsers = User::query()
                ->whereIn('email', $emails)
                ->get()
                ->keyBy('email');
        }

        foreach ($dataset as $record) {
            /** @var User|null $user */
            $user = $existingUsers[$record['email']] ?? null;

            if (! $user) {
                continue;
            }

            if (Schema::hasTable('user_addresses')) {
                DB::table('user_addresses')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'province_id' => $record['address']['province_id'],
                        'city_id' => $record['address']['city_id'],
                        'district_id' => $record['address']['district_id'],
                        'subdistrict_id' => $record['address']['subdistrict_id'],
                        'name' => $record['name'],
                        'phone' => $record['phone'],
                        'zip_code' => $record['address']['zip'],
                        'address' => $record['address']['street'],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]
                );
            }

            if (! $user->hasRole(User::ROLE_ADMIN)) {
                $user->assignRole(User::ROLE_USER);
            }
        }

        info(sprintf('DataSeeder: demo users total now %d.', User::query()->whereIn('email', $emails)->count()));
    }

    protected function seedBlogCategories(): void
    {
        if (! Schema::hasTable('blog_categories')) {
            info('DataSeeder: blog_categories table missing, skip blog category seeding.');

            return;
        }

        $timestamp = $this->referenceDate->toMutable();

        foreach (self::BLOG_CATEGORY_DEFINITIONS as $definition) {
            BlogCategory::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]
            );
        }

        $this->blogCategoryMap = BlogCategory::query()
            ->whereIn('slug', array_column(self::BLOG_CATEGORY_DEFINITIONS, 'slug'))
            ->pluck('id', 'slug')
            ->toArray();

        info(sprintf('DataSeeder: seeded %d blog categories.', count($this->blogCategoryMap)));
    }

    protected function seedBlogTags(): void
    {
        if (! Schema::hasTable('blog_tags')) {
            info('DataSeeder: blog_tags table missing, skip blog tag seeding.');

            return;
        }

        $timestamp = $this->referenceDate->toMutable();

        foreach (self::BLOG_TAG_DEFINITIONS as $slug) {
            $name = Str::headline(str_replace('-', ' ', $slug));

            BlogTag::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]
            );
        }

        $this->blogTagMap = BlogTag::query()
            ->whereIn('slug', self::BLOG_TAG_DEFINITIONS)
            ->pluck('name', 'slug')
            ->toArray();

        $this->blogTagSlugToId = BlogTag::query()
            ->whereIn('slug', self::BLOG_TAG_DEFINITIONS)
            ->pluck('id', 'slug')
            ->toArray();

        info(sprintf('DataSeeder: seeded %d blog tags.', count($this->blogTagSlugToId)));
    }

    protected function seedBlogs(): void
    {
        if (! Schema::hasTable('blogs')) {
            info('DataSeeder: blogs table missing, skip blog seeding.');

            return;
        }

        if (empty($this->blogCategoryMap)) {
            $this->seedBlogCategories();
        }

        if (empty($this->blogTagSlugToId)) {
            $this->seedBlogTags();
        }

        $mediaStats = [
            'url_success' => 0,
            'placeholder' => 0,
            'skipped' => 0,
        ];

        $totalBlogs = count(self::BLOG_TITLES);

        foreach (self::BLOG_TITLES as $index => $rawTitle) {
            $title = $this->validateAndPolishTitle($rawTitle);
            $slug = $this->slugify($title);
            $topic = $this->topicFromTitleSlug($slug);
            $content = $this->generateBlogContent($title, $slug, $topic);
            $minRead = $this->estimateMinRead($content);
            $date = $this->spreadDateForIndex($index);
            $timestamp = $date->format('Y-m-d H:i:s');
            $isPublish = $index < 20;
            $viewsCount = ($index % 8) * 25;
            $categoryId = $this->pickCategoryId($index);
            $userId = $this->pickUserIdOrNull($index);
            $description = Str::limit(
                sprintf('Ilustrasi artikel "%s" mengenai %s.', $title, $this->topicLabel($topic)),
                160
            );

            DB::table('blogs')->updateOrInsert(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'content' => $content,
                    'min_read' => $minRead,
                    'featured_image_description' => $description,
                    'is_publish' => $isPublish,
                    'views_count' => $viewsCount,
                    'blog_category_id' => $categoryId,
                    'user_id' => $userId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]
            );

            /** @var Blog|null $blog */
            $blog = Blog::query()->where('slug', $slug)->first();

            if (! $blog) {
                continue;
            }

            if (Schema::hasTable('blog_tag_blog') && ! empty($this->blogTagSlugToId)) {
                $tagSlugs = $this->pickTagsFor($slug);
                $tagIds = array_values(array_filter(
                    array_map(fn(string $tagSlug) => $this->blogTagSlugToId[$tagSlug] ?? null, $tagSlugs)
                ));

                if (! empty($tagIds)) {
                    $blog->tags()->sync($tagIds);
                }
            }

            $mediaResult = $this->attachBlogHero($blog, $topic, $description);
            $mediaStats[$mediaResult] = ($mediaStats[$mediaResult] ?? 0) + 1;
        }

        info(sprintf(
            'DataSeeder: seeded %d blogs (media via URL: %d, placeholder: %d, skipped: %d).',
            $totalBlogs,
            $mediaStats['url_success'] ?? 0,
            $mediaStats['placeholder'] ?? 0,
            $mediaStats['skipped'] ?? 0
        ));
    }

    protected function validateAndPolishTitle(string $title): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $title));
        $words = array_values(array_filter(explode(' ', $normalized)));

        $supplements = [
            'yang Praktis',
            'untuk Penggemar Parfum',
            'dalam Kehidupan Sehari-hari',
        ];

        $supplementIndex = 0;

        while (count($words) < 10 && $supplementIndex < count($supplements)) {
            $words = array_merge($words, explode(' ', $supplements[$supplementIndex]));
            $supplementIndex++;
        }

        while (count($words) < 10) {
            $words[] = 'Parfum';
        }

        if (count($words) > 18) {
            $removable = ['yang', 'dan', 'agar', 'sampai', 'serta', 'dengan', 'hingga', 'karena'];
            $filtered = array_values(array_filter($words, function ($word) use ($removable) {
                return ! in_array(Str::lower($word), $removable, true);
            }));

            if (count($filtered) >= 10) {
                $words = $filtered;
            }
        }

        if (count($words) > 18) {
            $words = array_slice($words, 0, 18);
        }

        if (! empty($words)) {
            $words[0] = Str::ucfirst($words[0]);
        }

        return implode(' ', $words);
    }

    protected function slugify(string $text): string
    {
        $base = Str::slug($text);

        if ($base === '') {
            $base = 'blog';
        }

        $slug = $base;
        $suffix = 2;

        while (isset($this->blogSlugRegistry[$slug])) {
            $slug = $base . '-v' . $suffix;
            $suffix++;
        }

        $this->blogSlugRegistry[$slug] = true;

        return $slug;
    }

    protected function estimateMinRead(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return (int) max(1, ceil($wordCount / 200));
    }

    protected function spreadDateForIndex(int $index): CarbonImmutable
    {
        return $this->referenceDate
            ->subDays($index % 90)
            ->setTime(10 + ($index % 7), 15, 0);
    }

    protected function pickCategoryId(int $index): ?int
    {
        if (empty($this->blogCategoryMap)) {
            return null;
        }

        $categoryIds = array_values($this->blogCategoryMap);

        return $categoryIds[$index % count($categoryIds)] ?? null;
    }

    protected function pickUserIdOrNull(int $index): ?int
    {
        $userIds = $this->userIds();

        if (empty($userIds)) {
            return null;
        }

        return $userIds[$index % count($userIds)] ?? null;
    }

    protected function pickTagsFor(string $titleSlug): array
    {
        $map = $this->keywordTagMap();
        $selected = [];

        foreach ($map as $keyword => $tagSlug) {
            if (Str::contains($titleSlug, $keyword)) {
                $selected[] = $tagSlug;
            }
        }

        $selected = array_values(array_unique($selected));

        $fallback = ['designer', 'layering', 'longevity', 'storage', 'projection', 'wardrobe'];

        foreach ($fallback as $tagSlug) {
            if (count($selected) >= 6) {
                break;
            }

            $selected[] = $tagSlug;
            $selected = array_values(array_unique($selected));
        }

        if (count($selected) < 3) {
            $selected = array_merge($selected, array_slice($fallback, 0, 3));
            $selected = array_values(array_unique($selected));
        }

        return array_slice($selected, 0, 6);
    }

    protected function topicFromTitleSlug(string $slug): string
    {
        $map = $this->topicKeywordMap();

        foreach ($map as $topic => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($slug, $keyword)) {
                    return $topic;
                }
            }
        }

        return 'default';
    }

    protected function pickImageCandidatesFor(string $topic): array
    {
        if ($this->blogImageUrlMap === null) {
            $this->blogImageUrlMap = $this->loadBlogImageUrlMap();
        }

        $fallback = $this->topicFallbackImages();
        $candidates = $this->blogImageUrlMap[$topic] ?? ($fallback[$topic] ?? []);

        if (empty($candidates)) {
            $candidates = $this->blogImageUrlMap['default'] ?? ($fallback['default'] ?? []);
        }

        $candidates = array_values(array_filter($candidates, fn($url) => $this->isHttps($url)));

        if (empty($candidates)) {
            $candidates = $fallback['default'] ?? [];
        }

        return array_values(array_unique($candidates));
    }

    protected function loadBlogImageUrlMap(): array
    {
        return $this->topicFallbackImages();
    }

    protected function topicFallbackImages(): array
    {
        $topics = [
            'citrus',
            'floral',
            'woody',
            'oriental',
            'gourmand',
            'oud',
            'aquatic',
            'layering',
            'designer',
            'niche',
            'storage',
            'testing',
            'default',
        ];

        $map = [];

        foreach ($topics as $topic) {
            $map[$topic] = $this->picsumSeedSet('blog-topic-' . $topic, 3, 1600, 1000);
        }

        return $map;
    }

    protected function isHttps(string $url): bool
    {
        return Str::startsWith(Str::lower($url), 'https://');
    }

    protected function urlOk(string $url): bool
    {
        if (! $this->isHttps($url)) {
            return false;
        }

        try {
            $head = Http::timeout(5)
                ->withHeaders(['Accept' => 'image/*'])
                ->head($url);

            if ($this->responseIndicatesImage($head)) {
                return true;
            }

            if (in_array($head->status(), [301, 302, 303, 307, 308, 403, 405], true)) {
                $get = Http::timeout(8)
                    ->withHeaders(['Accept' => 'image/*', 'Range' => 'bytes=0-1023'])
                    ->get($url);

                if ($this->responseIndicatesImage($get)) {
                    return true;
                }
            }
        } catch (Throwable) {
            try {
                $get = Http::timeout(8)
                    ->withHeaders(['Accept' => 'image/*', 'Range' => 'bytes=0-1023'])
                    ->get($url);

                if ($this->responseIndicatesImage($get)) {
                    return true;
                }
            } catch (Throwable) {
                return false;
            }
        }

        return false;
    }

    protected function responseIndicatesImage(?Response $response): bool
    {
        if (! $response || ! $response->successful()) {
            return false;
        }

        $contentType = (string) $response->header('Content-Type');

        return Str::startsWith(Str::lower($contentType), 'image/');
    }

    protected function downloadToTemp(string $url, string $prefix = 'asset'): ?string
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders(['Accept' => 'image/*'])
                ->get($url);

            if (! $this->responseIndicatesImage($response)) {
                return null;
            }

            $baseDir = storage_path('app/tmp/' . $prefix . '-images');
            File::ensureDirectoryExists($baseDir);

            $path = parse_url($url, PHP_URL_PATH) ?? '';
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if ($extension === '') {
                $extension = 'jpg';
            }

            $fileName = sprintf('%s-%s.%s', $prefix, substr(sha1($url), 0, 12), $extension);
            $fullPath = $baseDir . DIRECTORY_SEPARATOR . $fileName;

            File::put($fullPath, $response->body());

            return $fullPath;
        } catch (Throwable) {
            return null;
        }
    }

    protected function suggestFileName(string $topic, string $reference): string
    {
        $extension = 'jpg';

        if ($this->isHttps($reference)) {
            $path = parse_url($reference, PHP_URL_PATH) ?? '';
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext !== '') {
                $extension = $ext;
            }
        } else {
            $ext = strtolower(pathinfo($reference, PATHINFO_EXTENSION));
            if ($ext !== '') {
                $extension = $ext;
            }
        }

        return sprintf('%s-%s.%s', $topic, substr(sha1($reference), 0, 12), $extension);
    }

    protected function placeholderPath(): string
    {
        $path = database_path('data/seeders/blog_images/placeholder.jpg');
        File::ensureDirectoryExists(dirname($path));

        if (! File::exists($path)) {
            File::put($path, base64_decode(self::BLOG_PLACEHOLDER_BASE64));
        }

        return $path;
    }

    protected function attachBlogHero(Blog $blog, string $topic, string $description): string
    {
        if ($blog->hasMedia(Blog::MEDIA_COLLECTION_NAME)) {
            return 'skipped';
        }

        foreach ($this->pickImageCandidatesFor($topic) as $candidate) {
            $signature = sha1($candidate);

            if ($this->mediaAlreadyAttached($blog, Blog::MEDIA_COLLECTION_NAME, $signature)) {
                return 'skipped';
            }

            if (! $this->isHttps($candidate) || ! $this->urlOk($candidate)) {
                info(sprintf('DataSeeder: skipped blog image candidate %s for %s (unreachable).', $candidate, $blog->slug));

                continue;
            }

            $downloadedPath = $this->downloadToTemp($candidate, 'blog');

            if (! $downloadedPath) {
                info(sprintf('DataSeeder: failed downloading blog image candidate %s for %s.', $candidate, $blog->slug));

                continue;
            }

            try {
                $blog
                    ->addMedia($downloadedPath)
                    ->usingFileName($this->suggestFileName($topic, $candidate))
                    ->withCustomProperties([
                        'source_url' => $candidate,
                        'source_signature' => $signature,
                        'featured_image_description' => $description,
                    ])
                    ->preservingOriginal()
                    ->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);

                File::delete($downloadedPath);

                info(sprintf('DataSeeder: attached blog media from %s.', $candidate));

                if (empty($blog->featured_image_description)) {
                    $blog->forceFill(['featured_image_description' => $description])->saveQuietly();
                }

                return 'url_success';
            } catch (Throwable $exception) {
                File::delete($downloadedPath);
                info(sprintf('DataSeeder: failed attaching blog media from %s (%s).', $candidate, $exception->getMessage()));
            }
        }

        try {
            $blog
                ->addMedia($this->placeholderPath())
                ->usingFileName($this->suggestFileName($topic, 'placeholder.jpg'))
                ->withCustomProperties([
                    'source_url' => null,
                    'source_signature' => 'placeholder',
                    'featured_image_description' => $description,
                ])
                ->preservingOriginal()
                ->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);

            info(sprintf('DataSeeder: attached placeholder image for blog %s.', $blog->slug));
        } catch (Throwable $exception) {
            info(sprintf('DataSeeder: failed attaching placeholder image for blog %s (%s).', $blog->slug, $exception->getMessage()));
        }

        if (empty($blog->featured_image_description)) {
            $blog->forceFill(['featured_image_description' => $description])->saveQuietly();
        }

        return 'placeholder';
    }

    protected function generateBlogContent(string $title, string $slug, string $topic): string
    {
        $topicDescriptor = $this->topicDescriptor($topic);
        $topicLabel = $this->topicLabel($topic);

        $introParagraphs = [
            "Memahami dunia wewangian tidak selalu mudah, dan judul \"$title\" hadir sebagai panduan yang menautkan rasa penasaran dengan pengetahuan praktis. Setiap langkah dalam memilih dan memakai parfum selalu berkelindan dengan $topicDescriptor, sehingga membaca artikel ini membantu pembaca menata ekspektasi secara realistis. Kami menyiapkan contoh, analogi, serta referensi yang mudah dicerna agar pengalaman belajar tetap menyenangkan. Pendekatan bertahap membuat isi artikel tetap relevan baik untuk pemula maupun kolektor parfum. Dengan cara itu, pembelajaran berlangsung tanpa nadah menggurui, melainkan sebagai percakapan hangat.",
            "Kebutuhan informasi yang runtut membuat kami merancang artikel ini seperti sesi konsultasi personal yang sistematis. Pembaca diajak mengenali istilah teknis seperti silase, longevity, hingga konsentrasi parfum dengan konteks sehari-hari. Bahasa yang digunakan tetap ringan namun akurat sehingga tidak menimbulkan salah tafsir. Ketika menyebut $topicDescriptor, kami selalu menempelkan contoh nyata agar konsep tidak mengawang. Setiap paragraf menekankan bahwa memilih parfum adalah perjalanan yang membutuhkan observasi, pencatatan, dan kejujuran terhadap selera pribadi.",
            "Artikel ini juga memproyeksikan kebiasaan baru yang bisa diterapkan langsung sesaat setelah selesai membaca. Kami menjelaskan peran batch code, uji kulit, serta pentingnya menyusun catatan aroma layaknya jurnal sederhana. Setiap pembaca difasilitasi dengan daftar periksa yang mudah diikuti. Dengan begitu, $topicDescriptor tidak sekadar istilah abstrak, melainkan bagian dari ritme harian yang menyenangkan. Pendekatan edukatif ini membantu pembaca merasa percaya diri ketika berdialog dengan konsultan parfum atau teman komunitas.",
            "Kami sadar bahwa perjalanan membangun koleksi parfum penuh dengan opini yang saling bertabrakan. Karena itu, artikel ini memberi ruang untuk perspektif yang beragam, termasuk pengalaman pengguna pemula hingga penyuka niche. Saat membahas $topicDescriptor, kami menautkannya dengan dinamika musim, aktivitas, dan batas etika ruang publik. Pendekatan ini memastikan pembaca memahami kapan sebuah wangi patut diperkuat, dilapis, atau justru diistirahatkan. Tujuan akhirnya adalah menyusun keputusan yang membawa kenyamanan pribadi sekaligus menghormati lingkungan sekitar.",
            "Seluruh isi ditulis dalam Bahasa Indonesia agar pembaca menemukan kosakata yang familiar dan mudah diterapkan. Kami memasukkan istilah asli seperti top notes, heart notes, dan base notes dengan penjelasan kontekstual. Masing-masing subbab menyuguhkan alur pemikiran yang konsisten: mulai dari teori, latihan praktis, kesalahan umum, kemudian contoh nyata. Dengan format seperti ini, $topicDescriptor berubah menjadi rangkaian langkah terukur yang bisa dibuktikan hasilnya. Pembaca akan mendapatkan manfaat nyata tanpa harus menyaring beragam sumber asing terlebih dahulu.",
        ];

        $knowledgeParagraphs = [
            "Membedakan konsentrasi parfum adalah fondasi penting sebelum menilai kualitasnya. Eau de Toilette, Eau de Parfum, dan Extrait memiliki kadar minyak aromatik berbeda sehingga durasi wanginya juga berlainan. Dalam artikel ini kami memetakan angka rata-rata konsentrasi sekaligus contoh bagaimana $topicDescriptor bereaksi pada masing-masing jenis. Penjelasan tentang top, heart, dan base notes disertai ilustrasi waktu evaporasi agar pembaca memahami mengapa aroma berubah sepanjang hari. Kami juga menyisipkan istilah silase, longevity, dan projection supaya pembaca familiar ketika membaca ulasan profesional.",
            "Selain konsentrasi, struktur olfactory pyramid memberikan gambaran perjalanan aroma. Di bagian ini kami menguraikan hubungan antara accords dan komposisi bahan baku alami maupun sintetis. Pembaca diperkenalkan pada keluarga aroma seperti citrus, floral, woody, oriental, sampai gourmand sehingga mudah menelusuri preferensi pribadi. Kami menautkan $topicDescriptor dengan kombinasi bahan yang cocok melalu bagan sederhana. Pengetahuan dasar ini wajib dikuasai agar proses layering, sampling, ataupun pemilihan parfum untuk hadiah tidak bergantung pada tebakan semata.",
            "Kami juga menelaah regulasi IFRA, isu allergen, serta dampak reformulasi terhadap aroma. Informasi tersebut dibawakan dengan bahasa mudah agar pembaca memahami mengapa wangi favorit bisa berubah dari tahun ke tahun. Artikel ini memaparkan daftar bahan yang kerap dibatasi serta cara membaca komposisi secara kritis. Pembahasan mengenai penyimpanan, oksidasi, dan peran cahaya langsung juga dijelaskan secara ilmiah. Dengan demikian, $topicDescriptor tidak hanya bermakna romantik, melainkan juga menyangkut tanggung jawab keamanan dan kualitas produk.",
        ];

        $practiceParagraphs = [
            "Bagian praktik dirancang seperti modul latihan harian. Kami memandu pembaca membuat jurnal penciuman yang memuat catatan tanggal, cuaca, dan aktivitas. Latihan ini membantu mengevaluasi bagaimana $topicDescriptor bereaksi pada kulit dan pakaian. Kami mengajarkan teknik layering dua hingga tiga parfum dengan pendekatan konservatif, kemudian memberikan variasi lebih berani untuk yang sudah percaya diri. Setiap resep layering dilengkapi estimasi silase dan durasi agar pembaca bisa mengukur keberhasilan secara objektif.",
            "Kami juga memuat panduan sampling yang efisien sehingga anggaran bisa dialokasikan dengan bijak. Artikel ini mengulas cara memesan decant, mengecek reputasi penjual, hingga menyimpan vial mini dengan aman. Sebagai bagian dari praktik, pembaca diajak menilai projection dan longevity dengan metode sederhana menggunakan kertas blotter dan pencatatan waktu. Semua langkah dijelaskan langkah-demi-langkah supaya $topicDescriptor terasa terkelola. Pembaca diberi tips kapan sebaiknya melakukan full wear test sebelum memutuskan membeli botol penuh.",
            "Keseharian di iklim tropis menjadi fokus pembahasan tersendiri. Kami menjelaskan cara menyesuaikan intensitas semprotan berdasarkan suhu, kelembapan, dan ventilasi ruangan. Ada contoh kombinasi body wash, lotion, dan parfum agar wangi sinkron tanpa berlebihan. Artikel ini juga membahas strategi membawa atomizer traveling, termasuk etika penggunaannya di ruang publik. Penjelasan menyeluruh tersebut membuat $topicDescriptor hadir sebagai panduan gaya hidup, bukan sekadar teori yang sulit diterapkan.",
        ];

        $mistakeParagraphs = [
            "Kesalahan umum yang sering terjadi kami rangkum agar pembaca tidak perlu mengulangi pengalaman pahit orang lain. Banyak pengguna menyemprot parfum terlalu dekat ke kulit, tidak menunggu drydown, atau menilai parfum hanya dari strip tester. Artikel ini mengulas solusi praktis seperti mengatur jarak semprot, mencoba pada beberapa titik nadi, serta memberi jeda minimal lima belas menit sebelum mengambil keputusan. Kami menegaskan bahwa memahami $topicDescriptor menuntut kesabaran dan observasi konsisten.",
            "Kami juga menyinggung kesalahan dalam penyimpanan, khususnya membiarkan botol terpapar panas atau sinar matahari langsung. Solusinya adalah menyiapkan kotak tertutup, memakai silica gel secukupnya, dan meminimalisir udara masuk dengan menutup botol rapat. Bagi yang gemar layering, kesalahan mencampur aroma yang sama-sama pekat dapat dihindari dengan memilih satu parfum sebagai anchor dan satu lagi sebagai aksen. Penyesuaian kecil seperti ini menjaga $topicDescriptor tetap harmonis sepanjang hari.",
        ];

        $mistakeList = [
            '- Melewatkan uji kulit dan hanya mengandalkan blotter menyebabkan keputusan pembelian mudah menyesal.',
            '- Menyemprot terlalu banyak di ruang tertutup membuat orang sekitar tidak nyaman meski aromanya berkualitas.',
            '- Menyimpan parfum di kamar mandi mempercepat oksidasi karena fluktuasi temperatur dan kelembapan.',
            '- Mengabaikan catatan allergen pada label dapat memicu iritasi kulit dan menurunkan pengalaman pakai.',
            '- Tidak mencatat durasi silase membuat evaluasi longevity menjadi bias dan sulit dibandingkan.',
        ];

        $caseParagraphs = [
            "Untuk memperjelas penerapan, kami menghadirkan studi kasus berdasarkan rutinitas pekerja kantor di Jakarta. Ia membutuhkan parfum segar pada pagi hari, opsi layering untuk rapat penting, serta sesuatu yang hangat untuk makan malam. Dengan memahami $topicDescriptor, kami menyarankan kombinasi citrus light pada pagi hari, floral woody saat rapat, dan gourmand lembut untuk malam. Setiap pilihan disertai alasan mengenai silase, longevity, serta kesesuaian terhadap etika ruang kerja.",
            "Contoh lain datang dari penggemar niche yang sering bepergian antar kota. Ia memerlukan strategi penyimpanan agar koleksi tetap stabil, termasuk menyiapkan wadah anti guncangan dan kantong pendingin portabel. Artikel ini memandu pembaca memilih parfum berkarakter resin atau musk yang tahan cuaca, sekaligus memberi peringatan tentang batas keamanan membawa parfum di kabin pesawat. Pendekatan ini menunjukkan bahwa $topicDescriptor dapat disesuaikan dengan gaya hidup mobile tanpa kehilangan kesan glamor.",
            "Studi kasus terakhir berfokus pada hadiah parfum bagi sahabat yang baru mengenal dunia wewangian. Kami menunjukkan cara menyaring preferensi melalui pertanyaan tentang aktivitas, warna favorit, dan makanan kesukaan. Dari sana, rekomendasi parfum dibatasi pada tiga opsi dengan tingkat intensitas berbeda sehingga penerima bisa mengeksplorasi tanpa kewalahan. Strategi ini mengajak pembaca menerapkan $topicDescriptor sebagai alat empati, bukan sekadar selera pribadi yang dipaksakan.",
        ];

        $summaryBullets = [
            '- Kuasai istilah dasar seperti silase, longevity, dan konsentrasi agar evaluasi parfum lebih objektif.',
            '- Catat pengalaman harian untuk memetakan respon kulit serta menemukan kombinasi layering yang aman.',
            '- Terapkan etika pemakaian di ruang publik supaya kenyamanan pribadi sejalan dengan rasa hormat pada orang lain.',
            '- Rawat koleksi dengan penyimpanan yang stabil, jauh dari panas, cahaya langsung, dan kelembapan berlebih.',
            '- Teruslah bereksperimen secara bijak karena dunia parfum adalah perjalanan panjang yang penuh wawasan baru.',
            '',
            'Bagikan temuan Anda kepada komunitas parfum lokal agar semakin banyak orang menikmati wangi secara bertanggung jawab.',
        ];

        $contentParts = [
            implode("\n\n", $introParagraphs),
            "## Dasar dan Ilmu\n\n" . implode("\n\n", $knowledgeParagraphs),
            "## Praktik dan Tips\n\n" . implode("\n\n", $practiceParagraphs),
            "## Kesalahan Umum\n\n" . implode("\n\n", $mistakeParagraphs) . "\n\n" . implode("\n", $mistakeList),
            "## Studi Kasus dan Contoh\n\n" . implode("\n\n", $caseParagraphs),
            "## Ringkasan\n\n" . implode("\n", $summaryBullets),
        ];

        $content = implode("\n\n", $contentParts);
        $wordCount = str_word_count(strip_tags($content));

        while ($wordCount < 1000) {
            $content .= "\n\n" . $this->additionalInsightParagraph($topicDescriptor);
            $wordCount = str_word_count(strip_tags($content));
        }

        return $content;
    }

    protected function additionalInsightParagraph(string $topicDescriptor): string
    {
        return "Setiap kali Anda mengevaluasi perubahan aroma, sempatkan menuliskan catatan singkat mengenai suasana hati dan lingkungan sekitar. Kebiasaan tersebut membantu menafsirkan bagaimana $topicDescriptor beradaptasi dengan kondisi cuaca, tingkat stres, atau bahkan konsumsi kafein harian. Semakin banyak data pribadi yang terkumpul, semakin akurat pula strategi layering, rotasi botol, serta penentuan intensitas semprot. Kebiasaan reflektif ini membuat perjalanan wewangian terasa personal, mindful, dan penuh apresiasi terhadap detail yang kerap terlewat.";
    }

    protected function demoUserDataset(): array
    {
        $records = [];
        $firstNames = self::DEMO_USER_FIRST_NAMES;
        $lastNames = self::DEMO_USER_LAST_NAMES;
        $templates = self::DEMO_ADDRESS_TEMPLATES;
        $firstCount = count($firstNames);
        $lastCount = count($lastNames);
        $templateCount = count($templates);

        for ($i = 0; $i < 50; $i++) {
            $first = $firstNames[$i % $firstCount];
            $primaryLast = $lastNames[$i % $lastCount];
            $secondaryLast = $lastNames[($i + 5) % $lastCount];

            $name = $i % 4 === 3
                ? sprintf('%s %s %s', $first, $secondaryLast, $primaryLast)
                : sprintf('%s %s', $first, $primaryLast);

            $emailSlug = Str::slug($name, '.');
            $email = sprintf('%s%02d@demo.local', $emailSlug, $i + 1);
            $phone = sprintf('62812%06d', 200000 + $i);

            $template = $templates[$i % $templateCount];
            $street = sprintf(
                '%s Blok %s No.%d',
                $template['street'],
                chr(65 + ($i % 26)),
                3 + ($i % 15)
            );

            $records[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => [
                    'province_id' => $template['province_id'],
                    'city_id' => $template['city_id'],
                    'district_id' => $template['district_id'],
                    'subdistrict_id' => $template['subdistrict_id'],
                    'zip' => $template['zip'],
                    'street' => $street,
                ],
            ];
        }

        return $records;
    }

    protected function topicLabel(string $topic): string
    {
        return [
            'citrus' => 'aroma citrus yang segar',
            'floral' => 'aroma floral yang lembut',
            'woody' => 'nuansa kayu yang hangat',
            'oriental' => 'karakter oriental yang kaya',
            'gourmand' => 'sentuhan gourmand manis',
            'oud' => 'kedalaman oud yang eksotis',
            'aquatic' => 'aroma aquatic yang ringan',
            'layering' => 'strategi layering parfum',
            'designer' => 'pilihan parfum designer',
            'niche' => 'koleksi niche eksklusif',
            'storage' => 'perawatan dan penyimpanan parfum',
            'testing' => 'teknik pengujian parfum',
            'default' => 'dunia parfum secara umum',
        ][$topic] ?? 'dunia parfum secara umum';
    }

    protected function topicDescriptor(string $topic): string
    {
        return [
            'citrus' => 'aroma citrus yang cerah, penuh energi, dan cocok untuk suasana tropis',
            'floral' => 'gugusan aroma floral yang romantis dan bernuansa elegan',
            'woody' => 'lapisan kayu, vetiver, serta resin yang memberi kesan dewasa',
            'oriental' => 'perpaduan rempah, amber, dan balsam yang hangat',
            'gourmand' => 'nuansa gourmand yang memadukan manisnya dessert dan rempah',
            'oud' => 'karakter oud yang dalam, resinous, dan bertekstur mewah',
            'aquatic' => 'aroma aquatic yang bersih, segar, dan mudah disukai',
            'layering' => 'strategi layering parfum yang menuntut keseimbangan harmoni',
            'designer' => 'kumpulan rilis designer dengan popularitas tinggi',
            'niche' => 'koleksi niche yang berani mengeksplorasi bahan tak biasa',
            'storage' => 'protokol penyimpanan parfum yang menjaga stabilitas komposisi',
            'testing' => 'metode pengujian parfum yang sistematis dan mudah diikuti',
            'default' => 'rangkaian elemen parfum yang saling berkaitan',
        ][$topic] ?? 'rangkaian elemen parfum yang saling berkaitan';
    }

    protected function topicKeywordMap(): array
    {
        return [
            'citrus' => ['citrus', 'tropis', 'segarnya'],
            'floral' => ['floral', 'bunga', 'rose', 'jasmine'],
            'woody' => ['woody', 'kayu', 'vetiver', 'patchouli'],
            'oriental' => ['oriental', 'amber', 'resin'],
            'gourmand' => ['gourmand', 'manis'],
            'oud' => ['oud'],
            'aquatic' => ['aquatic', 'outdoor', 'olahraga'],
            'layering' => ['layering', 'sinkron'],
            'designer' => ['designer'],
            'niche' => ['niche', 'signature'],
            'storage' => ['menyimpan', 'storage', 'oksidasi'],
            'testing' => ['mencoba', 'sampling', 'decant', 'blotter', 'testing'],
            'default' => [],
        ];
    }

    protected function keywordTagMap(): array
    {
        return [
            'citrus' => 'citrus',
            'floral' => 'floral',
            'woody' => 'woody',
            'oriental' => 'oriental',
            'fougere' => 'fougere',
            'chypre' => 'chypre',
            'gourmand' => 'gourmand',
            'musk' => 'musk',
            'amber' => 'amber',
            'oud' => 'oud',
            'vetiver' => 'vetiver',
            'patchouli' => 'patchouli',
            'rose' => 'rose',
            'jasmine' => 'jasmine',
            'edp' => 'edp',
            'edt' => 'edt',
            'extrait' => 'extrait',
            'cologne' => 'cologne',
            'longevity' => 'longevity',
            'sillage' => 'sillage',
            'projection' => 'projection',
            'niche' => 'niche',
            'designer' => 'designer',
            'layering' => 'layering',
            'kantor' => 'office-safe',
            'profesional' => 'office-safe',
            'date' => 'date-night',
            'malam' => 'date-night',
            'tropis' => 'summer',
            'hujan' => 'winter',
            'ifra' => 'ifra',
            'reformulasi' => 'reformulation',
            'allergen' => 'allergen',
            'menyimpan' => 'storage',
            'wardrobe' => 'wardrobe',
            'signature' => 'wardrobe',
            'sampling' => 'sampling',
            'decant' => 'decant',
            'outdoor' => 'outdoor',
            'body' => 'body-care',
            'aromaterapi' => 'aromatherapy',
            'accord' => 'accords',
        ];
    }

    private function resolveCouponProductTable(): ?string
    {
        if (Schema::hasTable('coupon_product')) {
            return 'coupon_product';
        }

        if (Schema::hasTable('coupon_products')) {
            return 'coupon_products';
        }

        return null;
    }

    private function productMatchesCouponCode(Product $product, string $lowerCode): bool
    {
        $lowerSlug = Str::lower($product->slug);
        $brandSlug = Str::lower(optional($product->brand)->slug ?? '');

        if (str_contains($lowerCode, 'laundry')) {
            return Str::contains($lowerSlug, 'laundry');
        }

        if (str_contains($lowerCode, 'botol') || str_contains($lowerCode, 'bottle')) {
            return Str::contains($lowerSlug, ['botol', 'bottle']);
        }

        if (str_contains($lowerCode, 'niche')) {
            if ($brandSlug !== '' && in_array($brandSlug, $this->nicheBrandSlugs(), true)) {
                return true;
            }

            return Str::contains($lowerSlug, 'niche');
        }

        if (str_contains($lowerCode, 'travel') || str_contains($lowerCode, 'decant')) {
            return Str::contains($lowerSlug, ['travel', 'decant']);
        }

        if (str_contains($lowerCode, 'mist')) {
            return Str::contains($lowerSlug, ['mist', 'spray']);
        }

        if (
            str_contains($lowerCode, 'parfum')
            || str_contains($lowerCode, 'signature')
            || str_contains($lowerCode, 'spray')
        ) {
            return Str::contains($lowerSlug, ['parfum', 'eau', 'extrait', 'cologne', 'spray']);
        }

        return Str::contains($lowerSlug, ['parfum', 'eau', 'extrait', 'cologne', 'mist']);
    }

    /**
     * @return array<int, string>
     */
    private function nicheBrandSlugs(): array
    {
        return [
            'mfk',
            'creed',
            'le-labo',
            'byredo',
            'diptyque',
            'kilian-paris',
            'maison-margiela',
        ];
    }

    protected function bannerDefinitions(): array
    {
        return [
            ['img' => 'https://picsum.photos/id/1011/1600/600', 'desc' => 'Koleksi parfum terbaru sudah hadir. Temukan wangi favorit edisi 2025.', 'cta' => '/collections/new-arrivals'],
            ['img' => 'https://picsum.photos/id/1015/1600/600', 'desc' => 'Terlaris minggu ini—dipilih pelanggan. Wangi aman untuk aktivitas sehari-hari.', 'cta' => '/collections/best-sellers'],
            ['img' => 'https://picsum.photos/id/1025/1600/600', 'desc' => 'Signature scent pilihan, elegan dan mudah diingat.', 'cta' => '/collections/signature'],
            ['img' => 'https://picsum.photos/id/1003/1600/600', 'desc' => 'Segar, ringan, cocok iklim tropis. Cocok untuk aktivitas siang.', 'cta' => '/collections/fresh-citrus'],
            ['img' => 'https://picsum.photos/id/1067/1600/600', 'desc' => 'Hangat, mewah, dengan karakter yang berkelas.', 'cta' => '/collections/oud-amber'],
            ['img' => 'https://picsum.photos/id/1040/1600/600', 'desc' => 'Hadiah parfum siap kirim: set curated untuk momen spesial.', 'cta' => '/collections/gifts'],
            ['img' => 'https://picsum.photos/id/1035/1600/600', 'desc' => 'Rekomendasi layering: padu padan aroma aman dan harmonis.', 'cta' => '/collections/layering'],
            ['img' => 'https://picsum.photos/id/1027/1600/600', 'desc' => 'Ukuran 10–30 ml, praktis untuk dibawa ke mana saja.', 'cta' => '/collections/travel'],
            ['img' => 'https://picsum.photos/id/1016/1600/600', 'desc' => 'Pewangi laundry premium: harum bersih, tahan lama.', 'cta' => '/collections/laundry'],
            ['img' => 'https://picsum.photos/id/1050/1600/600', 'desc' => 'Botol, atomizer, dan aksesori untuk koleksi parfum Anda.', 'cta' => '/collections/accessories'],
        ];
    }

    protected function bannerImageUrlByIndex(int $index): string
    {
        $definitions = $this->bannerDefinitions();

        if (isset($definitions[$index])) {
            return $definitions[$index]['img'];
        }

        $baseId = 1010 + $index;

        return sprintf('https://picsum.photos/id/%d/1600/600', $baseId);
    }

    protected function attachBannerImage(Banner $banner, string $url, string $description): string
    {
        $signature = sha1($url);

        if ($this->mediaAlreadyAttached($banner, Banner::MEDIA_COLLECTION_NAME, $signature)) {
            return 'skipped';
        }

        if ($banner->hasMedia(Banner::MEDIA_COLLECTION_NAME)) {
            return 'skipped';
        }

        if ($this->isHttps($url) && $this->urlOk($url)) {
            $downloadedPath = $this->downloadToTemp($url, 'banner');

            if ($downloadedPath) {
                try {
                    $banner
                        ->addMedia($downloadedPath)
                        ->usingFileName($this->suggestFileName('banner', $url))
                        ->withCustomProperties([
                            'source_url' => $url,
                            'source_signature' => $signature,
                            'image_description' => $description,
                        ])
                        ->preservingOriginal()
                        ->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);

                    File::delete($downloadedPath);

                    if (empty($banner->image_description)) {
                        $banner->forceFill(['image_description' => $description])->saveQuietly();
                    }

                    return 'url_success';
                } catch (Throwable $exception) {
                    File::delete($downloadedPath);
                    info(sprintf('DataSeeder: failed attaching banner media from %s (%s).', $url, $exception->getMessage()));
                }
            }
        }

        try {
            $banner
                ->addMedia($this->bannerPlaceholderPath())
                ->usingFileName($this->suggestFileName('banner', 'placeholder.jpg'))
                ->withCustomProperties([
                    'source_url' => null,
                    'source_signature' => 'placeholder',
                    'image_description' => $description,
                ])
                ->preservingOriginal()
                ->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);

            if (empty($banner->image_description)) {
                $banner->forceFill(['image_description' => $description])->saveQuietly();
            }

            info(sprintf('DataSeeder: attached placeholder banner image for %s.', $banner->id));
        } catch (Throwable $exception) {
            info(sprintf('DataSeeder: failed attaching placeholder banner image for %s (%s).', $banner->id, $exception->getMessage()));
        }

        return 'placeholder';
    }

    protected function bannerPlaceholderPath(): string
    {
        $path = database_path('data/seeders/banner_placeholder.jpg');
        File::ensureDirectoryExists(dirname($path));

        if (! File::exists($path)) {
            File::put($path, base64_decode(self::BANNER_PLACEHOLDER_BASE64));
        }

        return $path;
    }

    protected function mediaAlreadyAttached($model, string $collection, string $signature): bool
    {
        $mediaItems = $model->getMedia($collection);

        if ($mediaItems->isEmpty()) {
            return false;
        }

        return $mediaItems->contains(function ($media) use ($signature) {
            return $media->getCustomProperty('source_signature') === $signature;
        });
    }

    protected function picsumSeedUrl(string $seed, int $width, int $height): string
    {
        return sprintf('https://picsum.photos/seed/%s/%d/%d', rawurlencode($seed), $width, $height);
    }

    protected function picsumSeedSet(string $prefix, int $count, int $width, int $height): array
    {
        return collect(range(1, $count))->map(function (int $index) use ($prefix, $width, $height) {
            return $this->picsumSeedUrl($prefix . '-' . $index, $width, $height);
        })->all();
    }

    private function genericPerfumeImages(): array
    {
        return $this->picsumSeedSet('perfume-generic', 3, 1200, 1200);
    }
}
