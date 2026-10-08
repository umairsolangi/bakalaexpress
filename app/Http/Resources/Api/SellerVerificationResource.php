<?php

namespace App\Http\Resources\Api;

use App\Models\SellerVerification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerVerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SellerVerification $this */
        $docs = is_array($this->documents) ? $this->documents : [];
        $resolvedDocs = array_map(function ($path) {
            if (empty($path)) {
                return null;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . ltrim($path, '/'));
        }, $docs);

        return [
            'id' => (int) $this->id,
            'seller_id' => (int) $this->seller_id,
            'status' => (string) $this->status,
            'business_description' => (string) $this->business_description,
            'reason_for_verification' => (string) $this->reason_for_verification,
            'documents' => array_values(array_filter($resolvedDocs)),
            'rejection_reason' => $this->rejection_reason,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
        ];
    }
}
