<?php

namespace App\Http\Requests\Api;

class SellerCatalogListRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:catalog_categories,id',
            'is_active' => 'nullable|in:0,1,true,false',
            'stock' => 'nullable|string|in:low,out,in',
            'sort' => 'nullable|string|in:name,price,stock,updated_at',
            'direction' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:30',
        ];
    }
}
