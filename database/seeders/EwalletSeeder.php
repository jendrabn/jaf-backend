<?php

namespace Database\Seeders;

use App\Models\Ewallet;
use Illuminate\Database\Seeder;

class EwalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ewallet::create([
            'name' => 'ShopeePay',
            'account_name' => 'JAF Parfum\'s Store',
            'account_username' => 'jafparfumsstore',
            'phone' => '08123456789',
        ]);
    }
}
