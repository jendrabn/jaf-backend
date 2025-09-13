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
            ['name' => 'Wahana',                           'code' => 'wahana', 'status' => 'inactive'],
            ['name' => 'Lion Parcel',                      'code' => 'lion',   'status' => 'active'],
            ['name' => 'Citra Van Titipan Kilat (TIKI)',   'code' => 'tiki',   'status' => 'inactive'],
            ['name' => 'ID Express',                       'code' => 'ide',    'status' => 'inactive'],
            ['name' => 'J&T Express',                      'code' => 'jnt',    'status' => 'active'],
            ['name' => 'Ninja Xpress',                     'code' => 'ninja',  'status' => 'inactive'],
            ['name' => 'POS Indonesia (POS)',              'code' => 'pos',    'status' => 'active'],
            ['name' => 'SiCepat Express',                  'code' => 'sicepat', 'status' => 'active'],
            ['name' => 'Satria Antaran Prima',             'code' => 'sap',    'status' => 'inactive'],
            ['name' => 'Royal Express Indonesia (REX)',    'code' => 'rex',    'status' => 'inactive'],
            ['name' => 'Sentral Cargo',                    'code' => 'sentral', 'status' => 'inactive'],
            ['name' => 'Jalur Nugraha Ekakurir (JNE)',     'code' => 'jne',    'status' => 'active'],
        ];

        Courier::insert($couriers);
    }
}
