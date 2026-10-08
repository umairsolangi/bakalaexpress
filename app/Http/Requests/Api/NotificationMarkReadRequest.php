<?php

namespace App\Http\Requests\Api;

use App\Services\Api\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class NotificationMarkReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['sometimes', 'array'],
            'ids.*' => ['string'],
            'all' => ['sometimes', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Invalid notification mark read request.',
                'VALIDATION_ERROR',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}
