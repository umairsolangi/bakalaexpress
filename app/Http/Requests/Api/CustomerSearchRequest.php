<?php

namespace App\Http\Requests\Api;

class CustomerSearchRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:50'],
            'sector' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'in:shops,products,all'],
            'category_id' => ['nullable', 'integer', 'exists:catalog_categories,id'],
            'category' => ['nullable', 'integer', 'exists:catalog_categories,id'],
            'in_stock' => ['nullable', 'boolean'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }
}
