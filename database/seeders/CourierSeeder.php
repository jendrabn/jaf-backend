<?php

namespace Database\Seeders;

use App\Models\Courier;
use Database\Factories\FactoryData;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (FactoryData::courierDefinitions() as $courier) {
            Courier::query()->updateOrCreate(
                ['code' => $courier['code']],
                [
                    'name' => $courier['name'],
                    'is_active' => $courier['is_active'],
                ]
            );
        }
    }
}
