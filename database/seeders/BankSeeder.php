<?php

namespace Database\Seeders;

use App\Models\Bank;
use Database\Factories\FactoryData;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (FactoryData::paymentBanks() as $bank) {
            Bank::query()->updateOrCreate(
                ['code' => $bank['code']],
                [
                    'name' => $bank['name'],
                    'account_name' => $bank['account_name'],
                    'account_number' => $bank['account_number'],
                ]
            );
        }
    }
}
