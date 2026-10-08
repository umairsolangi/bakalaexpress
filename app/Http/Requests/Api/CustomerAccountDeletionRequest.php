<?php

namespace App\Http\Requests\Api;

use App\Services\Api\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerAccountDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Password is required for account deletion.',
                'VALIDATION_ERROR',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}
