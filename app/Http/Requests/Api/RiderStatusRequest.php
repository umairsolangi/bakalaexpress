<?php

namespace App\Http\Requests\Api;

class RiderStatusRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:online,offline'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status field is required.',
            'status.in' => "The selected status is invalid. Only 'online' or 'offline' may be set.",
        ];
    }
}
