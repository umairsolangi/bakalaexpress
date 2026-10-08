<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        
        // Get all seller IDs (users with sellerType = 3)
        $sellerIds = DB::table('sellers')
            ->where('accountIsApproved', 1)
            ->pluck('id');

        // Unsplash laundry related images
        $images = [
            'https://images.unsplash.com/photo-1545173168-9f1947eebb7f',
            'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60',
            'https://images.unsplash.com/photo-1582735689369-4fe89db7114c',
            'https://images.unsplash.com/photo-1604335399105-a0c585fd81a1',
            'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce'
        ];

        // Service types
        $serviceTypes = [
            'Wash & Fold',
            'Dry Cleaning',
            'Iron Only',
            'Wash & Iron',
            'Stain Removal',
            'Premium Wash',
            'Express Service',
            'Bulk Laundry'
        ];

        // Create 5 services for each seller
        foreach ($sellerIds as $sellerId) {
            for ($i = 0; $i < 5; $i++) {
                DB::table('services')->insert([
                    'seller_id' => $sellerId,
                    'service_name' => $faker->randomElement($serviceTypes),
                    'service_description' => $faker->paragraph(3),
                    'seller_city' => $faker->city,
                    'seller_area' => $faker->streetName,
                    'availability' => $faker->randomElement(['24/7', 'Mon-Fri', 'Mon-Sat', 'Weekends']),
                    'service_delivery_time' => $faker->randomElement(['24 Hours', '48 Hours', '72 Hours', 'Same Day']),
                    'seller_contact_no' => $faker->phoneNumber,
                    'service_price' => $faker->randomFloat(2, 10, 100),
                    'image' => $faker->randomElement($images),
                    'is_approved' => $faker->boolean(70), // 70% chance of being approved
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}