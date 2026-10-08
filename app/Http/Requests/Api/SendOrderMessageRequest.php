<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendOrderMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('message')) {
            $this->merge([
                'message' => strip_tags(trim((string) $this->input('message'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'The message field is required.',
            'message.min' => 'The message must be at least 1 character.',
            'message.max' => 'The message may not be greater than 1000 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'code' => 'VALIDATION_ERROR',
                'data' => (object) [],
                'errors' => $validator->errors(),
                'meta' => (object) [],
            ], 422)
        );
    }
}
