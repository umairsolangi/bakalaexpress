<?php

namespace App\Http\Requests\Api;

class SellerCatalogImportRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'global_product_ids' => 'nullable|array|min:1|max:100',
            'global_product_ids.*' => 'integer',
            'import_all_category' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasIds = !empty($this->input('global_product_ids'));
            $importAll = $this->boolean('import_all_category');

            if (!$hasIds && !$importAll) {
                $validator->errors()->add('global_product_ids', 'You must provide either global_product_ids or set import_all_category to true.');
            }
        });
    }
}
