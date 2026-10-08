<?php

namespace App\Http\Requests\Api;

class CustomerHomeRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sector' => ['nullable', 'string', 'max:50'],
            'near_area' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:catalog_categories,id'],
            'category' => ['nullable', 'integer', 'exists:catalog_categories,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'is_open' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }
}
