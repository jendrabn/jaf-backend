<?php

// database\seeders\ProductCategorySeeder.php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Parfum',
            'Parfum Laundry',
            'Pengharum Ruangan',
            'Botol Parfum Kosong',
            'Travel & Decant',
        ];

        foreach ($categories as $categoryName) {
            ProductCategory::query()->updateOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName)]
            );
        }
    }
}
