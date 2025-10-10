<?php

namespace Database\Seeders;

use App\Models\ProductBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
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

        foreach ($brands as $name) {
            ProductBrand::query()->updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }
    }
}
