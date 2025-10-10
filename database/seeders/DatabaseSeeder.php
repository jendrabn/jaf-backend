<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        File::cleanDirectory(storage_path('app/public'));

        $this->call([
            RolesAndPermissionsSeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            CourierSeeder::class,
            DataSeeder::class,
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
    }
}
