<?php

namespace App\Http\Requests\Api;

class RiderRegisterRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:riders,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],
            'cnic_number' => ['required', 'string', 'regex:/^[0-9]{13}$/', 'unique:riders,cnic_number'],
            'vehicle_type' => 'required|string|max:255',
            'vehicle_number' => 'required|string|max:255',
            'address' => 'required|string',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'cnic_front' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'cnic_back' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'license_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'vehicle_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'registration_book' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
        ];
    }
}
