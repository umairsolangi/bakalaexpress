<?php

namespace Database\Seeders;

use App\Models\Rider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RiderSeeder extends Seeder
{
    public function run(): void
    {
        Rider::updateOrCreate(
            ['email' => 'rider@bakalaexpress.com'],
            [
                'name' => 'Demo Rider',
                'phone' => '03111234567',
                'cnic_number' => '4210112345671',
                'vehicle_number' => 'KHI-1234',
                'address' => 'Street 10, Baldia Town, Karachi',
                'password' => Hash::make('rider123'),
                'vehicle_type' => 'bike',
                'current_lat' => 24.9491000,
                'current_long' => 66.9385000,
                'status' => 'online',
                'is_verified' => true,
                'is_approved' => true,
                'profile_image' => null,
                'cnic_front' => null,
                'cnic_back' => null,
                'license_image' => null,
                'vehicle_image' => null,
                'registration_book' => null,
            ]
        );
    }
}
