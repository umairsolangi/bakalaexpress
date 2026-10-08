<?php

namespace App\Http\Requests\Api;

class RiderHistoryRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.date_format' => "The 'from' filter must match the format YYYY-MM-DD.",
            'to.date_format' => "The 'to' filter must match the format YYYY-MM-DD.",
            'to.after_or_equal' => "The 'to' date must be a date after or equal to the 'from' date.",
            'per_page.max' => 'The per_page value cannot exceed 30.',
        ];
    }
}
