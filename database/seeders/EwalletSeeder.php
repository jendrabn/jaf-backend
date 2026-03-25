<?php

namespace Database\Seeders;

use App\Models\Ewallet;
use Database\Factories\FactoryData;
use Illuminate\Database\Seeder;

class EwalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (FactoryData::ewalletDefinitions() as $ewallet) {
            Ewallet::query()->updateOrCreate(
                ['name' => $ewallet['name']],
                [
                    'account_name' => $ewallet['account_name'],
                    'account_username' => $ewallet['account_username'],
                    'phone' => $ewallet['phone'],
                ]
            );
        }
    }
}
