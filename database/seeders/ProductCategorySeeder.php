<?php

// database\seeders\ProductCategorySeeder.php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Database\Factories\FactoryData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (FactoryData::productCategoryNames() as $categoryName) {
            ProductCategory::query()->updateOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName)]
            );
        }
    }
}
