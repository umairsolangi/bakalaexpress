<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

abstract class BaseApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $disallowedFields = ['sellerType', 'accountIsApproved', 'is_approved', 'is_verified'];
        foreach ($disallowedFields as $field) {
            if ($this->has($field)) {
                throw ValidationException::withMessages([
                    $field => ["The {$field} field cannot be specified in the request body."],
                ]);
            }
        }
    }
}
