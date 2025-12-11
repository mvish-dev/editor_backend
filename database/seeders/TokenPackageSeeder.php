<?php

namespace Database\Seeders;

use App\Models\TokenPackage;
use Illuminate\Database\Seeder;

class TokenPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncate
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        
        // Truncate existing records to avoid duplicates if run multiple times
        TokenPackage::truncate();

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $packages = [
            [
                'name' => 'Starter',
                'tokens' => 20,
                'price' => 199.00,
            ],
            [
                'name' => 'Pro',
                'tokens' => 50,
                'price' => 399.00,
            ],
            [
                'name' => 'Elite',
                'tokens' => 100,
                'price' => 699.00,
            ],
            [
                'name' => 'Business',
                'tokens' => 250,
                'price' => 1499.00,
            ],
        ];

        foreach ($packages as $package) {
            TokenPackage::create($package);
        }
    }
}
