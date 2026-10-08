<?php

namespace App\Http\Requests\Api;

class RiderDeliverOrderRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $isRequired = (bool) config('bakala_orders.require_delivery_proof', false);

        return [
            'proof_image' => [
                $isRequired ? 'required' : 'nullable',
                'file',
                'image',
                'mimes:jpeg,png,jpg',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'proof_image.required' => 'A delivery proof image is required to complete this delivery.',
            'proof_image.file' => 'The delivery proof must be a valid uploaded file.',
            'proof_image.image' => 'The delivery proof must be an image.',
            'proof_image.mimes' => 'The delivery proof image must be a file of type: jpeg, png, jpg.',
            'proof_image.max' => 'The delivery proof image must not be larger than 4096 kilobytes (4 MB).',
        ];
    }
}
