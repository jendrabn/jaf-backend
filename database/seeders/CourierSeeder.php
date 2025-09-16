<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $couriers = [
            ['name' => 'Wahana',                           'code' => 'wahana', 'is_active' => 0],
            ['name' => 'Lion Parcel',                      'code' => 'lion',   'is_active' => 1],
            ['name' => 'Citra Van Titipan Kilat (TIKI)',   'code' => 'tiki',   'is_active' => 0],
            ['name' => 'ID Express',                       'code' => 'ide',    'is_active' => 0],
            ['name' => 'J&T Express',                      'code' => 'jnt',    'is_active' => 1],
            ['name' => 'Ninja Xpress',                     'code' => 'ninja',  'is_active' => 0],
            ['name' => 'POS Indonesia (POS)',              'code' => 'pos',    'is_active' => 1],
            ['name' => 'SiCepat Express',                  'code' => 'sicepat', 'is_active' => 1],
            ['name' => 'Satria Antaran Prima',             'code' => 'sap',    'is_active' => 0],
            ['name' => 'Royal Express Indonesia (REX)',    'code' => 'rex',    'is_active' => 0],
            ['name' => 'Sentral Cargo',                    'code' => 'sentral', 'is_active' => 0],
            ['name' => 'Jalur Nugraha Ekakurir (JNE)',     'code' => 'jne',    'is_active' => 1],
        ];

        Courier::insert($couriers);
    }
}
