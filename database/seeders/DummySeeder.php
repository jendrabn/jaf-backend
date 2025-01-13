<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Ewallet;
use App\Models\PaymentEwallet;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\OrderItem;
use App\Models\PaymentBank;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        File::cleanDirectory(storage_path('app/public'));

        $this->call([
            RolesAndPermissionsSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            EwalletSeeder::class
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => 'password',
        ])->assignRole(User::ROLE_ADMIN);

        User::create([
            'name' => 'User',
            'email' => 'user@mail.com',
            'password' => 'password',
        ])->assignRole(User::ROLE_USER);

        Product::factory(100)->hasImages(3)->create();

        for ($i = 0; $i < 25; $i++) {
            Order::factory()
                ->has(OrderItem::factory(random_int(1, 5)), 'items')
                ->has(Invoice::factory()->has(Payment::factory()->has(PaymentBank::factory(), 'bank')))
                ->has(Shipping::factory())
                ->for(User::factory()->afterCreating(fn($user) => $user->assignRole(User::ROLE_USER))->create())
                ->create();
        }

        $ewallet = Ewallet::inRandomOrder()->first();

        for ($i = 0; $i < 25; $i++) {
            Order::factory()
                ->has(OrderItem::factory(random_int(1, 5)), 'items')
                ->has(Invoice::factory()
                    ->has(Payment::factory(state: [
                        'method' => 'ewallet',
                        'info' => [
                            'name' => $ewallet->name,
                            'account_name' => $ewallet->account_name,
                            'account_username' => $ewallet->account_username,
                            'phone' => $ewallet->phone
                        ]
                    ])
                        ->has(PaymentEwallet::factory(), 'ewallet')))
                ->has(Shipping::factory())
                ->for(User::factory()->afterCreating(fn($user) => $user->assignRole(User::ROLE_USER))->create())
                ->create();
        }


        BlogCategory::factory(5)->create();
        BlogTag::factory(10)->create();
        Blog::factory(25)->create();
    }
}
