<?php

namespace App\Http\Requests\Api;

class SellerVerificationSubmitRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_description' => ['required', 'string', 'min:20', 'max:2000'],
            'reason_for_verification' => ['required', 'string', 'min:20', 'max:2000'],
            'documents' => ['required', 'array', 'min:1', 'max:5'],
            'documents.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'verification_agreement' => ['required', 'boolean', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'documents.required' => 'At least one verification document is required.',
            'documents.*.max' => 'Each document must not exceed 5MB.',
            'documents.*.mimes' => 'Each document must be a PDF, JPG, JPEG, or PNG file.',
            'verification_agreement.accepted' => 'You must accept the verification agreement to proceed.',
        ];
    }
}
