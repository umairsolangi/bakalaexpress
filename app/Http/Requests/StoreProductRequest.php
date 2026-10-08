<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('seller')->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'seller_city' => 'required|string|max:255',
            'seller_area' => 'required|string|max:255',
            'seller_contact_no' => 'required|string|max:15',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'unit_type' => 'required|string|max:50',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
