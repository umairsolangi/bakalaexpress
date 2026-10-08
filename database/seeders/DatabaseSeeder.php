<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CatalogCategorySeeder::class,
            UserSeeder::class,
            SellerSeeder::class,
            RiderSeeder::class,
            GlobalProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
