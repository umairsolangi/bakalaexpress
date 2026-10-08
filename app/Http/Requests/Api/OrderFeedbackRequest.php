<?php

namespace App\Http\Requests\Api;

class OrderFeedbackRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'feedback' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'A rating is required.',
            'rating.between' => 'Rating must be an integer between 1 and 5.',
            'feedback.required' => 'Feedback text is required.',
            'feedback.max' => 'Feedback cannot exceed 1000 characters.',
        ];
    }
}
