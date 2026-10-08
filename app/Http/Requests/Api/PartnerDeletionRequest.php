<?php

namespace App\Http\Requests\Api;

use App\Services\Api\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PartnerDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Invalid request data.',
                'VALIDATION_ERROR',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}
