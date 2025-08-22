<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminWallet;

class AdminWalletSeeder extends Seeder
{
    public function run(): void
    {
        AdminWallet::firstOrCreate(
            ['name' => 'Main Company Wallet'],
            [
                'balance' => 0.00,
                'currency' => 'NGN',
            ]
        );
    }
}
