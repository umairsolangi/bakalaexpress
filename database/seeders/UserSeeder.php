<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bakala.pk'],
            [
                'sellerType' => 1,
                'name' => 'Demo Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('abc.1234'),
                'mobile' => '03001234567',
                'address' => 'Office #1, Baldia Town',
                'address2' => null,
                'city' => 'Karachi',
                'state' => 'Sindh',
                'zip' => '75760',
                'pickup_time' => 'morning',
                'is_verified' => true,
                'otp' => null,
                'otp_expires_at' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@bakalaexpress.com'],
            [
                'sellerType' => 2,
                'name' => 'Demo User',
                'email_verified_at' => now(),
                'password' => Hash::make('user12345'),
                'mobile' => '03007654321',
                'address' => 'House #12, Sector 4B, Baldia Town',
                'address2' => null,
                'city' => 'Karachi',
                'state' => 'Sindh',
                'zip' => '75760',
                'pickup_time' => 'evening',
                'is_verified' => true,
                'otp' => null,
                'otp_expires_at' => null,
            ]
        );
    }
}