<?php

namespace Database\Seeders;

use App\Models\ProductBrand;
use Database\Factories\FactoryData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (FactoryData::brandNames() as $name) {
            ProductBrand::query()->updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }
    }
}
