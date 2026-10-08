<?php

namespace App\Http\Resources\Api;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public customer review resource.
 *
 * Masks reviewer identity (e.g. "Sara A.") and never exposes user id, email, or other customer data.
 */
class CustomerReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Feedback $this */
        $rawName = trim((string) ($this->user?->name ?? 'Customer'));
        $parts = preg_split('/\s+/', $rawName, -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) > 1) {
            $reviewerName = $parts[0] . ' ' . strtoupper(substr(end($parts), 0, 1)) . '.';
        } else {
            $reviewerName = !empty($parts[0]) ? $parts[0] : 'Customer';
        }

        return [
            'rating' => (int) $this->rating,
            'feedback' => (string) $this->feedback,
            'created_at' => $this->created_at?->toIso8601String(),
            'reviewer_name' => $reviewerName,
        ];
    }
}
