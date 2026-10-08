<?php

namespace App\Http\Requests\Api;

class SellerRegisterRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:sellers,email',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'sector' => 'nullable|string|in:4A,4B,4C',
            'catalog_category_id' => 'nullable|exists:catalog_categories,id',
            'near_areas' => 'nullable|array|min:1',
            'near_areas.*' => 'string|max:100',
            'full_address' => 'nullable|string|min:10|max:500',
            'terms' => 'required',
        ];
    }
}
