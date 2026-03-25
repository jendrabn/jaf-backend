<?php

namespace Database\Factories;

use Illuminate\Support\Str;

final class FactoryData
{
    private const BRAND_NAMES = [
        'Dior',
        'Chanel',
        'Gucci',
        'YSL',
        'Tom Ford',
        'Jo Malone London',
        'Maison Francis Kurkdjian',
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
        'JAF Bottles',
        'JAF Atelier',
        'JAF Home',
    ];

    private const PRODUCT_CATEGORIES = [
        'Parfum',
        'Parfum Laundry',
        'Pengharum Ruangan',
        'Travel & Decant',
        'Botol Parfum Kosong',
        'Body Mist',
        'Gift Set',
    ];

    private const BLOG_CATEGORIES = [
        'Panduan Parfum',
        'Review & Rekomendasi',
        'Ilmu Wewangian',
        'Tips Perawatan & Layering',
        'Bisnis & Tren Industri',
        'Panduan Belanja',
        'Signature Scent',
        'Parfum untuk Aktivitas',
    ];

    private const BLOG_TAGS = [
        'citrus',
        'floral',
        'woody',
        'amber',
        'oud',
        'musk',
        'gourmand',
        'fresh',
        'aquatic',
        'powdery',
        'green',
        'aromatic',
        'office-safe',
        'date-night',
        'summer',
        'winter',
        'layering',
        'storage',
        'projection',
        'longevity',
        'edp',
        'edt',
        'parfum',
        'extrait',
        'niche',
        'designer',
        'gift-guide',
        'tropical-weather',
        'daily-wear',
        'luxury',
    ];

    private const PRODUCT_VARIANTS = [
        ['brand' => 'Dior', 'line' => 'Sauvage', 'category' => 'Parfum', 'sex' => 1, 'concentration' => 'EDP', 'size' => 60, 'family' => 'fresh spicy woody', 'notes' => 'bergamot, lavender, dan ambroxan', 'tier' => 'designer'],
        ['brand' => 'Dior', 'line' => 'J\'adore', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDP', 'size' => 50, 'family' => 'floral luminous', 'notes' => 'ylang-ylang, jasmine, dan rose', 'tier' => 'designer'],
        ['brand' => 'Chanel', 'line' => 'Bleu de Chanel', 'category' => 'Parfum', 'sex' => 1, 'concentration' => 'EDP', 'size' => 100, 'family' => 'aromatic citrus woody', 'notes' => 'grapefruit, incense, dan cedar', 'tier' => 'designer'],
        ['brand' => 'Chanel', 'line' => 'Coco Mademoiselle', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDP', 'size' => 100, 'family' => 'amber floral', 'notes' => 'orange, patchouli, dan rose', 'tier' => 'designer'],
        ['brand' => 'Gucci', 'line' => 'Guilty Pour Homme', 'category' => 'Parfum', 'sex' => 1, 'concentration' => 'EDP', 'size' => 90, 'family' => 'spicy aromatic', 'notes' => 'lemon, lavender, dan patchouli', 'tier' => 'designer'],
        ['brand' => 'YSL', 'line' => 'Libre', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDP', 'size' => 90, 'family' => 'floral aromatic', 'notes' => 'lavender, orange blossom, dan vanilla', 'tier' => 'designer'],
        ['brand' => 'Tom Ford', 'line' => 'Oud Wood', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'EDP', 'size' => 50, 'family' => 'woody oriental', 'notes' => 'oud, cardamom, dan sandalwood', 'tier' => 'niche'],
        ['brand' => 'Maison Francis Kurkdjian', 'line' => 'Baccarat Rouge 540', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'EDP', 'size' => 70, 'family' => 'amber airy', 'notes' => 'saffron, jasmine, dan ambergris', 'tier' => 'niche'],
        ['brand' => 'Creed', 'line' => 'Aventus', 'category' => 'Parfum', 'sex' => 1, 'concentration' => 'EDP', 'size' => 100, 'family' => 'fruity smoky', 'notes' => 'pineapple, birch, dan musk', 'tier' => 'niche'],
        ['brand' => 'Byredo', 'line' => 'Gypsy Water', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'EDP', 'size' => 50, 'family' => 'woody aromatic', 'notes' => 'juniper, incense, dan vanilla', 'tier' => 'niche'],
        ['brand' => 'Le Labo', 'line' => 'Santal 33', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'EDP', 'size' => 50, 'family' => 'woody leathery', 'notes' => 'cardamom, iris, dan sandalwood', 'tier' => 'niche'],
        ['brand' => 'Diptyque', 'line' => 'Philosykos', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'EDP', 'size' => 75, 'family' => 'green woody', 'notes' => 'fig leaf, cedar, dan coconut', 'tier' => 'niche'],
        ['brand' => 'Giorgio Armani', 'line' => 'Si', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDP', 'size' => 100, 'family' => 'fruity chypre', 'notes' => 'blackcurrant, rose, dan patchouli', 'tier' => 'designer'],
        ['brand' => 'Hermes', 'line' => 'Terre d\'Hermes', 'category' => 'Parfum', 'sex' => 1, 'concentration' => 'EDT', 'size' => 100, 'family' => 'citrus mineral woody', 'notes' => 'orange, vetiver, dan cedar', 'tier' => 'designer'],
        ['brand' => 'Versace', 'line' => 'Eros', 'category' => 'Parfum', 'sex' => 1, 'concentration' => 'EDP', 'size' => 100, 'family' => 'fresh sweet aromatic', 'notes' => 'mint, tonka bean, dan vanilla', 'tier' => 'designer'],
        ['brand' => 'Prada', 'line' => 'Paradoxe', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDP', 'size' => 90, 'family' => 'amber floral', 'notes' => 'neroli, jasmine, dan amber', 'tier' => 'designer'],
        ['brand' => 'Lancome', 'line' => 'Idole', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDP', 'size' => 75, 'family' => 'clean floral', 'notes' => 'rose, jasmine, dan white musk', 'tier' => 'designer'],
        ['brand' => 'Montblanc', 'line' => 'Explorer', 'category' => 'Parfum', 'sex' => 1, 'concentration' => 'EDP', 'size' => 100, 'family' => 'woody aromatic', 'notes' => 'bergamot, patchouli, dan vetiver', 'tier' => 'designer'],
        ['brand' => 'Maison Margiela', 'line' => 'Jazz Club', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'EDT', 'size' => 100, 'family' => 'boozy tobacco', 'notes' => 'rum, tobacco leaf, dan vanilla', 'tier' => 'niche'],
        ['brand' => 'Carolina Herrera', 'line' => 'Good Girl', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDP', 'size' => 80, 'family' => 'sweet floral amber', 'notes' => 'tuberose, tonka bean, dan cacao', 'tier' => 'designer'],
        ['brand' => 'JAF Atelier', 'line' => 'Citrus Vetiver', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'EDP', 'size' => 50, 'family' => 'fresh woody', 'notes' => 'bergamot, neroli, dan vetiver', 'tier' => 'house'],
        ['brand' => 'JAF Atelier', 'line' => 'Velvet Rose Oud', 'category' => 'Parfum', 'sex' => 3, 'concentration' => 'Parfum', 'size' => 50, 'family' => 'rose oud amber', 'notes' => 'rose damascena, oud, dan amber', 'tier' => 'house'],
        ['brand' => 'JAF Atelier', 'line' => 'Morning Tea', 'category' => 'Parfum', 'sex' => 2, 'concentration' => 'EDT', 'size' => 100, 'family' => 'green citrus', 'notes' => 'green tea, lemon zest, dan musk', 'tier' => 'house'],
        ['brand' => 'JAF Home', 'line' => 'White Tea Linen', 'category' => 'Pengharum Ruangan', 'sex' => 3, 'concentration' => 'Room Spray', 'size' => 120, 'family' => 'clean musky', 'notes' => 'white tea, aldehydes, dan musk', 'tier' => 'home'],
        ['brand' => 'JAF Home', 'line' => 'Amber Santal', 'category' => 'Pengharum Ruangan', 'sex' => 3, 'concentration' => 'Room Spray', 'size' => 150, 'family' => 'warm woody', 'notes' => 'amber, sandalwood, dan tonka', 'tier' => 'home'],
        ['brand' => 'Downy', 'line' => 'Mystique Bloom', 'category' => 'Parfum Laundry', 'sex' => 3, 'concentration' => 'Laundry Perfume', 'size' => 500, 'family' => 'soft floral', 'notes' => 'violet, musk, dan powder accord', 'tier' => 'laundry'],
        ['brand' => 'Molto', 'line' => 'Blue Luxe', 'category' => 'Parfum Laundry', 'sex' => 3, 'concentration' => 'Laundry Perfume', 'size' => 900, 'family' => 'fresh musky', 'notes' => 'clean accord, lavender, dan musk', 'tier' => 'laundry'],
        ['brand' => 'Comfort', 'line' => 'Royal Silk', 'category' => 'Parfum Laundry', 'sex' => 3, 'concentration' => 'Laundry Perfume', 'size' => 800, 'family' => 'powdery floral', 'notes' => 'white floral, powder, dan musk', 'tier' => 'laundry'],
        ['brand' => 'JAF Bottles', 'line' => 'Amber Apothecary', 'category' => 'Botol Parfum Kosong', 'sex' => 3, 'concentration' => 'Bottle', 'size' => 30, 'family' => 'aksesori refill', 'notes' => 'kaca amber, sprayer metal, dan tutup premium', 'tier' => 'accessory'],
        ['brand' => 'JAF Bottles', 'line' => 'Travel Atomizer Matte Black', 'category' => 'Travel & Decant', 'sex' => 3, 'concentration' => 'Atomizer', 'size' => 10, 'family' => 'travel accessory', 'notes' => 'aluminium matte, vial isi ulang, dan pouch', 'tier' => 'accessory'],
        ['brand' => 'JAF Atelier', 'line' => 'Soft Peony Mist', 'category' => 'Body Mist', 'sex' => 2, 'concentration' => 'Body Mist', 'size' => 150, 'family' => 'light floral', 'notes' => 'peony, pear, dan clean musk', 'tier' => 'house'],
        ['brand' => 'JAF Atelier', 'line' => 'Ocean Citrus Mist', 'category' => 'Body Mist', 'sex' => 3, 'concentration' => 'Body Mist', 'size' => 150, 'family' => 'aquatic citrus', 'notes' => 'mandarin, sea breeze, dan musk', 'tier' => 'house'],
    ];

    private const BANNER_LINES = [
        ['description' => 'Kurasi parfum fresh citrus untuk cuaca tropis dan aktivitas siang hari.', 'url' => '/collections/fresh-citrus'],
        ['description' => 'Pilihan oud, amber, dan woody untuk karakter malam yang lebih tegas.', 'url' => '/collections/oud-amber'],
        ['description' => 'Bundle gift set parfum premium untuk hadiah ulang tahun dan momen spesial.', 'url' => '/collections/gift-set'],
        ['description' => 'Travel atomizer dan refill essentials agar signature scent tetap ikut bepergian.', 'url' => '/collections/travel-ready'],
        ['description' => 'Promo room spray dan parfum laundry untuk rumah yang wangi dan bersih.', 'url' => '/collections/home-fragrance'],
    ];

    private const BLOG_TITLES = [
        'Panduan Memilih Parfum Pertama yang Tepat untuk Aktivitas Harian',
        'Perbedaan EDT, EDP, Parfum, dan Extrait dalam Bahasa yang Mudah Dipahami',
        'Cara Menilai Longevity dan Projection Parfum Tanpa Tertipu First Impression',
        'Rekomendasi Parfum Segar untuk Iklim Tropis dan Mobilitas Tinggi',
        'Layering Parfum yang Aman untuk Kantor, Meeting, dan Acara Malam',
        'Mengapa Harga Parfum Bisa Mahal: Bahan Baku, Konsentrasi, dan Branding',
        'Memahami Top Notes, Heart Notes, dan Base Notes Saat Mencoba Wewangian',
        'Panduan Memilih Parfum Hadiah Berdasarkan Kepribadian dan Gaya Hidup',
        'Tips Menyimpan Koleksi Parfum Agar Tidak Cepat Oksidasi',
        'Niche versus Designer: Mana yang Lebih Cocok untuk Koleksi Anda',
        'Cara Membaca Deskripsi Fragrance Notes Saat Belanja Online',
        'Parfum Unisex yang Tetap Elegan untuk Siang hingga Malam',
    ];

    private const CONTACT_SUBJECTS = [
        'Saya butuh rekomendasi parfum citrus yang tahan untuk kerja 8 jam.',
        'Apakah ada opsi gift wrapping untuk order parfum premium minggu ini?',
        'Saya ingin tanya perbedaan antara ukuran 50ml dan 100ml untuk koleksi ini.',
        'Apakah stok travel atomizer matte black masih tersedia untuk pengiriman cepat?',
        'Saya ingin memastikan produk yang saya beli original dan tersegel resmi.',
        'Tolong bantu cek status pesanan saya yang berisi dua parfum dan satu room spray.',
    ];

    private const COUPON_CAMPAIGNS = [
        ['name' => 'Weekend Fragrance Picks', 'description' => 'Diskon akhir pekan untuk parfum designer pilihan dengan performa aman dipakai harian.', 'promo_type' => 'period', 'discount_type' => 'percentage', 'discount_amount' => 15, 'limit' => null, 'limit_per_user' => 1, 'start_offset' => -1, 'end_offset' => 2],
        ['name' => 'Luxury Scent Voucher', 'description' => 'Potongan nominal untuk pembelian parfum premium dan niche dengan nilai transaksi lebih tinggi.', 'promo_type' => 'limit', 'discount_type' => 'fixed', 'discount_amount' => 150000, 'limit' => 200, 'limit_per_user' => 1, 'start_offset' => -3, 'end_offset' => 14],
        ['name' => 'Room Fragrance Reset', 'description' => 'Promo untuk room spray dan home fragrance agar ruang kerja tetap bersih dan nyaman.', 'promo_type' => 'product', 'discount_type' => 'percentage', 'discount_amount' => 12, 'limit' => 120, 'limit_per_user' => 2, 'start_offset' => 0, 'end_offset' => 10],
        ['name' => 'Laundry Perfume Refill', 'description' => 'Potongan harga untuk repeat order parfum laundry dan kebutuhan rumah tangga beraroma lembut.', 'promo_type' => 'product', 'discount_type' => 'fixed', 'discount_amount' => 25000, 'limit' => 300, 'limit_per_user' => 3, 'start_offset' => -2, 'end_offset' => 7],
    ];

    private const PAYMENT_BANKS = [
        ['name' => 'BCA', 'code' => '014', 'account_name' => 'JAF PARFUMS', 'account_number' => '8888123456'],
        ['name' => 'Mandiri', 'code' => '008', 'account_name' => 'JAF PARFUMS', 'account_number' => '1320012345678'],
        ['name' => 'BNI', 'code' => '009', 'account_name' => 'JAF PARFUMS', 'account_number' => '00955000123'],
        ['name' => 'BRI', 'code' => '002', 'account_name' => 'JAF PARFUMS', 'account_number' => '002301234567503'],
    ];

    private const EWALLETS = ['GoPay', 'OVO', 'ShopeePay', 'Dana'];

    private const ADDRESS_TEMPLATES = [
        ['province_id' => 31, 'city_id' => 3173, 'district_id' => 3173030, 'subdistrict_id' => 3173030005, 'zip_code' => '12160', 'street' => 'Jl. Wijaya'],
        ['province_id' => 32, 'city_id' => 3273, 'district_id' => 3273020, 'subdistrict_id' => 3273020005, 'zip_code' => '40115', 'street' => 'Jl. Progo'],
        ['province_id' => 34, 'city_id' => 3471, 'district_id' => 3471030, 'subdistrict_id' => 3471030007, 'zip_code' => '55223', 'street' => 'Jl. Kaliurang'],
        ['province_id' => 35, 'city_id' => 3578, 'district_id' => 3578040, 'subdistrict_id' => 3578040005, 'zip_code' => '65145', 'street' => 'Jl. Soekarno Hatta'],
        ['province_id' => 36, 'city_id' => 3671, 'district_id' => 3671040, 'subdistrict_id' => 3671040004, 'zip_code' => '15111', 'street' => 'Jl. Ahmad Yani'],
    ];

    private const SHIPPING_SERVICES = [
        ['courier' => 'jne', 'courier_name' => 'Jalur Nugraha Ekakurir (JNE)', 'service' => 'REG', 'service_name' => 'Layanan Reguler', 'etd' => '1-2 hari'],
        ['courier' => 'jne', 'courier_name' => 'Jalur Nugraha Ekakurir (JNE)', 'service' => 'YES', 'service_name' => 'Yakin Esok Sampai', 'etd' => '1 hari'],
        ['courier' => 'tiki', 'courier_name' => 'Titipan Kilat', 'service' => 'REG', 'service_name' => 'Reguler Service', 'etd' => '2-3 hari'],
        ['courier' => 'pos', 'courier_name' => 'POS Indonesia', 'service' => 'Pos Reguler', 'service_name' => 'Pos Reguler', 'etd' => '2-4 hari'],
    ];

    private const NOTIFICATIONS = [
        ['category' => 'transaction', 'level' => 'success', 'title' => 'Pembayaran Berhasil Diterima', 'body' => 'Pembayaran pesanan parfum Anda sudah diverifikasi dan sedang disiapkan untuk diproses.', 'icon' => 'fas fa-credit-card'],
        ['category' => 'transaction', 'level' => 'info', 'title' => 'Pesanan Siap Dikirim', 'body' => 'Tim gudang sedang menyiapkan paket parfum dan travel atomizer Anda untuk pengiriman hari ini.', 'icon' => 'fas fa-box-open'],
        ['category' => 'promo', 'level' => 'info', 'title' => 'Weekend Perfume Drop', 'body' => 'Ada promo spesial untuk koleksi fresh citrus, floral office-safe, dan room spray terbaru.', 'icon' => 'fas fa-gift'],
        ['category' => 'account', 'level' => 'warning', 'title' => 'Profil Berhasil Diperbarui', 'body' => 'Data akun dan alamat pengiriman utama Anda telah diperbarui dengan aman.', 'icon' => 'fas fa-user-check'],
        ['category' => 'system', 'level' => 'info', 'title' => 'Fitur Wishlist Baru Tersedia', 'body' => 'Sekarang Anda bisa menyimpan parfum incaran dan memantau promo dari wishlist pribadi.', 'icon' => 'fas fa-bell'],
    ];

    private const FLASH_SALE_NAMES = [
        'Weekend Fragrance Rush',
        'Midnight Oud Drop',
        'Lunch Break Fresh Picks',
        'Payday Perfume Event',
        'Home Fragrance Happy Hour',
    ];

    private const TAXES = [
        ['name' => 'PPN', 'rate' => 11.00],
        ['name' => 'PPN Produk Wewangian', 'rate' => 11.00],
        ['name' => 'Pajak Penjualan Retail', 'rate' => 10.00],
        ['name' => 'PPN Retail Online', 'rate' => 11.00],
        ['name' => 'Pajak Transaksi Marketplace', 'rate' => 10.00],
    ];

    private const CAMPAIGNS = [
        ['name' => 'Launch Signature Citrus', 'subject' => 'Koleksi citrus terbaru untuk cuaca tropis sudah hadir', 'status' => 'draft'],
        ['name' => 'Weekend Designer Picks', 'subject' => 'Diskon parfum designer pilihan hanya sampai Minggu malam', 'status' => 'sending'],
        ['name' => 'Room Fragrance Refresh', 'subject' => 'Saatnya upgrade aroma rumah dengan room spray premium', 'status' => 'sent'],
    ];

    private const COURIERS = [
        ['name' => 'Jalur Nugraha Ekakurir (JNE)', 'code' => 'jne', 'is_active' => true],
        ['name' => 'POS Indonesia (POS)', 'code' => 'pos', 'is_active' => true],
        ['name' => 'TIKI', 'code' => 'tiki', 'is_active' => true],
        ['name' => 'J&T Express', 'code' => 'jnt', 'is_active' => true],
        ['name' => 'SiCepat Express', 'code' => 'sicepat', 'is_active' => true],
        ['name' => 'Lion Parcel', 'code' => 'lion', 'is_active' => false],
    ];

    /**
     * @return array<int, string>
     */
    public static function brandNames(): array
    {
        return self::BRAND_NAMES;
    }

    /**
     * @return array<int, string>
     */
    public static function productCategoryNames(): array
    {
        return self::PRODUCT_CATEGORIES;
    }

    /**
     * @return array<int, string>
     */
    public static function blogCategoryNames(): array
    {
        return self::BLOG_CATEGORIES;
    }

    /**
     * @return array<int, string>
     */
    public static function blogTagNames(): array
    {
        return self::BLOG_TAGS;
    }

    /**
     * @return array<int, array{name:string,code:string,account_name:string,account_number:string}>
     */
    public static function paymentBanks(): array
    {
        return self::PAYMENT_BANKS;
    }

    /**
     * @return array<int, array{name:string,account_name:string,account_username:string,phone:string}>
     */
    public static function ewalletDefinitions(): array
    {
        return array_map(function (string $name, int $index): array {
            return [
                'name' => $name,
                'account_name' => 'JAF PARFUMS',
                'account_username' => Str::slug($name, '').'jaf',
                'phone' => sprintf('081230000%03d', $index + 1),
            ];
        }, self::EWALLETS, array_keys(self::EWALLETS));
    }

    /**
     * @return array<int, array{name:string,code:string,is_active:bool}>
     */
    public static function courierDefinitions(): array
    {
        return self::COURIERS;
    }

    /**
     * @return array<int, array{name:string,rate:float}>
     */
    public static function taxDefinitions(): array
    {
        return self::TAXES;
    }

    public static function brand(): array
    {
        $name = fake()->unique()->randomElement(self::BRAND_NAMES);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    public static function category(): array
    {
        $name = fake()->unique()->randomElement(self::PRODUCT_CATEGORIES);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    public static function blogCategory(): array
    {
        $name = fake()->unique()->randomElement(self::BLOG_CATEGORIES);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    public static function blogTag(): array
    {
        $name = fake()->unique()->randomElement(self::BLOG_TAGS);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    public static function banner(): array
    {
        $banner = fake()->randomElement(self::BANNER_LINES);

        return [
            'image_description' => $banner['description'],
            'url' => $banner['url'],
            'order' => fake()->numberBetween(1, 12),
        ];
    }

    public static function product(): array
    {
        $variant = fake()->unique()->randomElement(self::productCandidates());
        $name = sprintf('%s %s %s %dml', $variant['brand'], $variant['line'], $variant['concentration'], $variant['size']);
        $price = self::productPrice($variant['tier'], $variant['size'], $variant['category']);
        $weight = self::productWeight($variant['size'], $variant['category']);

        return [
            'brand_name' => $variant['brand'],
            'brand_slug' => Str::slug($variant['brand']),
            'category_name' => $variant['category'],
            'category_slug' => Str::slug($variant['category']),
            'name' => $name,
            'slug' => Str::slug($name),
            'weight' => $weight,
            'price' => $price,
            'stock' => self::productStock($variant['tier'], $variant['category']),
            'description' => sprintf(
                '%s menghadirkan karakter %s dengan komposisi %s. Ukuran %dml ini cocok untuk %s dan disiapkan untuk pasar parfum Indonesia.',
                $name,
                $variant['family'],
                $variant['notes'],
                $variant['size'],
                $variant['category'] === 'Parfum' ? 'pemakaian harian maupun acara khusus' : 'kebutuhan perawatan aroma harian'
            ),
            'sex' => $variant['sex'],
        ];
    }

    public static function blog(): array
    {
        $title = fake()->randomElement(self::BLOG_TITLES);

        return [
            'title' => $title,
            'slug' => Str::slug($title.'-'.fake()->unique()->numerify('###')),
            'content' => self::blogContent($title),
            'featured_image_description' => 'Foto editorial yang menampilkan botol parfum dan suasana meja vanity premium.',
            'min_read' => fake()->numberBetween(4, 12),
            'is_publish' => fake()->boolean(85),
            'views_count' => fake()->numberBetween(120, 25000),
        ];
    }

    public static function contactMessage(): array
    {
        $status = fake()->randomElement(['new', 'in_progress', 'resolved', 'spam']);

        return [
            'message' => fake()->randomElement(self::CONTACT_SUBJECTS),
            'status' => $status,
            'notes' => in_array($status, ['resolved', 'in_progress'], true)
                ? fake()->randomElement([
                    'Pelanggan meminta rekomendasi parfum office-safe dengan budget menengah.',
                    'Tim CS sudah mengarahkan ke opsi designer 50ml dan travel atomizer.',
                    'Kasus sedang ditindaklanjuti ke tim warehouse untuk pengecekan stok.',
                ])
                : null,
        ];
    }

    public static function coupon(): array
    {
        $campaign = fake()->randomElement(self::COUPON_CAMPAIGNS);

        return [
            'name' => $campaign['name'],
            'description' => $campaign['description'],
            'promo_type' => $campaign['promo_type'],
            'code' => Str::upper(Str::slug($campaign['name'], '')).fake()->unique()->numerify('##'),
            'discount_type' => $campaign['discount_type'],
            'discount_amount' => $campaign['discount_amount'],
            'limit' => $campaign['limit'],
            'limit_per_user' => $campaign['limit_per_user'],
            'start_date' => now()->addDays($campaign['start_offset'])->toDateString(),
            'end_date' => now()->addDays($campaign['end_offset'])->toDateString(),
            'is_active' => true,
        ];
    }

    public static function paymentBank(): array
    {
        return fake()->randomElement(self::PAYMENT_BANKS);
    }

    public static function paymentEwallet(): array
    {
        $name = fake()->randomElement(self::EWALLETS);
        $customerName = fake()->name();

        return [
            'name' => $name,
            'account_name' => $customerName,
            'account_username' => Str::slug($customerName, '').fake()->numberBetween(10, 99),
            'phone' => fake()->numerify('08##########'),
        ];
    }

    public static function address(): array
    {
        $template = fake()->randomElement(self::ADDRESS_TEMPLATES);
        $recipient = fake()->name();

        return [
            'province_id' => $template['province_id'],
            'city_id' => $template['city_id'],
            'district_id' => $template['district_id'],
            'subdistrict_id' => $template['subdistrict_id'],
            'name' => $recipient,
            'phone' => fake()->numerify('08##########'),
            'zip_code' => $template['zip_code'],
            'address' => sprintf('%s No.%d, %s Residence', $template['street'], fake()->numberBetween(1, 199), fake()->randomElement(['Blok A', 'Blok B', 'Cluster Sakura', 'Cluster Magnolia'])),
        ];
    }

    public static function shipping(): array
    {
        $service = fake()->randomElement(self::SHIPPING_SERVICES);

        return array_merge($service, [
            'weight' => fake()->numberBetween(500, 4000),
            'tracking_number' => 'JAF'.fake()->unique()->numerify('##########'),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped']),
        ]);
    }

    public static function notification(): array
    {
        $notification = fake()->randomElement(self::NOTIFICATIONS);

        return array_merge($notification, [
            'url' => fake()->optional(0.8)->randomElement([
                '/orders/'.fake()->numberBetween(1000, 9999),
                '/wishlist',
                '/promotions/weekend-fragrance',
                '/account/notifications',
            ]),
            'meta' => fake()->optional(0.8)->randomElement([
                ['order_id' => fake()->numberBetween(1000, 9999)],
                ['promo_code' => Str::upper(fake()->bothify('JAF??##'))],
                ['collection' => fake()->randomElement(['fresh-citrus', 'oud-amber', 'gift-set'])],
            ]),
            'read_at' => fake()->optional(0.35)->dateTimeBetween('-10 days', 'now'),
        ]);
    }

    public static function flashSale(): array
    {
        $startAt = fake()->dateTimeBetween('-2 days', '+5 days');
        $endAt = (clone $startAt)->modify('+'.fake()->numberBetween(6, 18).' hours');

        return [
            'name' => fake()->randomElement(self::FLASH_SALE_NAMES),
            'description' => 'Diskon terbatas untuk parfum pilihan dengan stok promo yang dibatasi per pengguna.',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_active' => true,
        ];
    }

    public static function tax(): array
    {
        $tax = fake()->unique()->randomElement(self::TAXES);

        return $tax;
    }

    public static function campaign(): array
    {
        $campaign = fake()->randomElement(self::CAMPAIGNS);
        $scheduledAt = fake()->dateTimeBetween('-3 days', '+7 days');
        $sentAt = $campaign['status'] === 'sent'
            ? fake()->dateTimeBetween('-3 days', 'now')
            : null;

        return [
            'name' => $campaign['name'],
            'subject' => $campaign['subject'],
            'content' => self::campaignContent($campaign['subject']),
            'status' => $campaign['status'],
            'scheduled_at' => $scheduledAt,
            'sent_at' => $sentAt,
        ];
    }

    private static function productPrice(string $tier, int $size, string $category): int
    {
        if ($category === 'Parfum Laundry') {
            return self::roundToThousand((int) ($size * fake()->numberBetween(260, 420)));
        }

        if ($category === 'Pengharum Ruangan') {
            return self::roundToThousand((int) ($size * fake()->numberBetween(2200, 3600)));
        }

        if ($category === 'Body Mist') {
            return self::roundToThousand((int) ($size * fake()->numberBetween(1800, 2800)));
        }

        if (in_array($category, ['Travel & Decant', 'Botol Parfum Kosong'], true)) {
            return self::roundToThousand((int) ($size * fake()->numberBetween(6000, 15000)));
        }

        $rate = match ($tier) {
            'niche' => fake()->numberBetween(48000, 76000),
            'designer' => fake()->numberBetween(22000, 36000),
            default => fake()->numberBetween(9000, 18000),
        };

        return self::roundToThousand($size * $rate);
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private static function productCandidates(): array
    {
        $candidates = [];

        foreach (self::PRODUCT_VARIANTS as $variant) {
            foreach (self::sizeOptions($variant['category'], (int) $variant['size']) as $size) {
                $candidate = $variant;
                $candidate['size'] = $size;
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    private static function productWeight(int $size, string $category): int
    {
        if ($category === 'Parfum Laundry') {
            return $size + fake()->numberBetween(120, 260);
        }

        if ($category === 'Pengharum Ruangan') {
            return $size + fake()->numberBetween(180, 260);
        }

        if (in_array($category, ['Travel & Decant', 'Botol Parfum Kosong'], true)) {
            return $size + fake()->numberBetween(60, 140);
        }

        return $size + fake()->numberBetween(180, 320);
    }

    private static function productStock(string $tier, string $category): int
    {
        if (in_array($category, ['Parfum Laundry', 'Pengharum Ruangan', 'Body Mist'], true)) {
            return fake()->numberBetween(40, 180);
        }

        if (in_array($category, ['Travel & Decant', 'Botol Parfum Kosong'], true)) {
            return fake()->numberBetween(80, 260);
        }

        return match ($tier) {
            'niche' => fake()->numberBetween(6, 24),
            'designer' => fake()->numberBetween(12, 48),
            default => fake()->numberBetween(18, 60),
        };
    }

    private static function blogContent(string $title): string
    {
        return implode("\n\n", [
            sprintf('%s membahas dunia parfum dari sudut pandang yang relevan untuk pembaca Indonesia. Fokus utamanya adalah membantu memilih wewangian yang terasa mewah, tetap nyaman dipakai, dan sesuai dengan konteks aktivitas sehari-hari.', $title),
            'Pembahasan dimulai dari struktur aroma, performa di kulit, sampai cara membaca komposisi notes tanpa harus bergantung pada istilah yang terlalu teknis. Dengan pendekatan ini, pembaca bisa membedakan mana parfum yang aman untuk kantor, mana yang lebih cocok untuk acara malam, dan mana yang ideal dijadikan hadiah.',
            'Artikel juga menghubungkan budget dengan kualitas bahan baku, konsentrasi, dan ukuran botol. Itu penting karena harga parfum tidak berdiri sendiri, melainkan dipengaruhi oleh komposisi, positioning brand, serta pengalaman pemakaian jangka panjang.',
            'Di bagian penutup, pembaca diarahkan pada langkah praktis: menguji parfum di kulit, memberi waktu drydown, lalu menyesuaikan pilihan dengan iklim tropis dan rutinitas pribadi. Hasilnya, keputusan membeli parfum jadi lebih rasional sekaligus tetap menyenangkan.',
        ]);
    }

    private static function campaignContent(string $subject): string
    {
        return implode("\n\n", [
            $subject,
            'Email ini menonjolkan kurasi parfum yang relevan dengan kebutuhan pemakai modern: aman untuk kerja, tetap berkarakter, dan cocok untuk iklim tropis.',
            'Setiap produk disertai deskripsi notes, rekomendasi momen pakai, serta alasan mengapa koleksi tersebut layak diprioritaskan dalam wishlist pelanggan.',
            'Bagian penutup mengarahkan pelanggan ke landing page promo atau koleksi terbaru dengan bahasa yang ringkas dan konversi-friendly.',
        ]);
    }

    private static function roundToThousand(int $amount): int
    {
        return (int) (round($amount / 1000) * 1000);
    }

    /**
     * @return array<int, int>
     */
    private static function sizeOptions(string $category, int $defaultSize): array
    {
        return match ($category) {
            'Parfum' => array_values(array_unique([$defaultSize, 30, 50, 75, 100, 125])),
            'Parfum Laundry' => array_values(array_unique([$defaultSize, 500, 800, 900, 1000])),
            'Pengharum Ruangan' => array_values(array_unique([$defaultSize, 100, 120, 150, 250])),
            'Body Mist' => array_values(array_unique([$defaultSize, 100, 150, 200])),
            'Travel & Decant' => array_values(array_unique([$defaultSize, 5, 10, 15])),
            'Botol Parfum Kosong' => array_values(array_unique([$defaultSize, 10, 30, 50])),
            default => [$defaultSize],
        };
    }
}
