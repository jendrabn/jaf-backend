<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bank::create([
            'code' => '014',
            'name' => 'BCA (Bank Central Asia)',
            'account_name' => 'Muhammad',
            'account_number' => '0240597799',
        ]);
    }
}
