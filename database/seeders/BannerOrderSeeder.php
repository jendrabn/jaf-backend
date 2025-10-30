<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = Banner::orderBy('id')->get();

        foreach ($banners as $index => $banner) {
            $banner->update(['order' => $index + 1]);
        }
    }
}
