<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Rider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'cnic_number',
        'vehicle_number',
        'address',
        'vehicle_type',
        'current_lat',
        'current_long',
        'status',
        'is_verified',
        'is_approved',
        'profile_image',
        'cnic_front',
        'cnic_back',
        'license_image',
        'vehicle_image',
        'registration_book',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'current_lat' => 'decimal:7',
        'current_long' => 'decimal:7',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
