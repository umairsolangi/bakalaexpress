<?php

namespace App\Http\Requests\Api;

class SellerBulkPriceRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'mode' => 'required|string|in:increase_percent,decrease_percent,reset_to_base',
            'percent' => 'nullable|numeric',
            'scope' => 'nullable|string|in:all,category,custom',
            'category_id' => 'nullable|integer|exists:catalog_categories,id',
            'listing_ids' => 'nullable|array|min:1|max:100',
            'listing_ids.*' => 'integer',
            'preview' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $mode = $this->input('mode');
            $percent = $this->input('percent');

            if (in_array($mode, ['increase_percent', 'decrease_percent'], true)) {
                if ($percent === null || !is_numeric($percent)) {
                    $validator->errors()->add('percent', 'A numeric percent value is required for price adjustments.');
                } elseif ($mode === 'increase_percent' && ((float) $percent < 0.1 || (float) $percent > 100)) {
                    $validator->errors()->add('percent', 'Increase percentage must be between 0.1% and 100%.');
                } elseif ($mode === 'decrease_percent' && ((float) $percent < 0.1 || (float) $percent > 50)) {
                    $validator->errors()->add('percent', 'Decrease percentage must be between 0.1% and 50%.');
                }
            }

            $hasListingIds = !empty($this->input('listing_ids'));
            $scope = $this->input('scope');

            if (!$hasListingIds && empty($scope)) {
                $validator->errors()->add('scope', 'You must specify listing_ids or provide a scope (all or category).');
            }

            if ($scope === 'category' && empty($this->input('category_id'))) {
                $validator->errors()->add('category_id', 'category_id is required when scope is category.');
            }
        });
    }
}
