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
        File::cleanDirectory(storage_path('app/public'));

        $this->call([
            RolesAndPermissionsSeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class,
            CourierSeeder::class,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => 'password',
        ])->assignRole(User::ROLE_ADMIN);

        $admin->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        $user = User::create([
            'name' => 'User',
            'email' => 'user@mail.com',
            'password' => 'password',
        ])->assignRole(User::ROLE_USER);
    }
}
