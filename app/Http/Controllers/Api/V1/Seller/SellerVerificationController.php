<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SellerVerificationSubmitRequest;
use App\Http\Resources\Api\SellerVerificationResource;
use App\Models\Seller;
use App\Models\SellerVerification;
use App\Services\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerVerificationController extends Controller
{
    /**
     * Helper to get authenticated seller instance.
     */
    protected function seller(Request $request): Seller
    {
        /** @var Seller $seller */
        $seller = $request->user();
        return $seller;
    }

    /**
     * GET /api/v1/seller/verification/status
     * Check verification status for the authenticated seller.
     */
    public function status(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        $verification = SellerVerification::where('seller_id', $seller->id)->latest('id')->first();

        return ApiResponse::success([
            'is_verified' => (bool) ($seller->is_verified ?? false),
            'has_submitted' => $verification !== null,
            'verification' => $verification ? new SellerVerificationResource($verification) : null,
        ], 'Verification status retrieved successfully.');
    }

    /**
     * POST /api/v1/seller/verification/submit
     * Submit or re-submit a verification request with documents.
     */
    public function submit(SellerVerificationSubmitRequest $request): JsonResponse
    {
        $seller = $this->seller($request);

        // Check if seller already has a pending or approved verification
        $existingPendingOrApproved = SellerVerification::where('seller_id', $seller->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingPendingOrApproved) {
            return ApiResponse::error(
                "You already have a {$existingPendingOrApproved->status} verification request.",
                'VERIFICATION_ALREADY_EXISTS',
                [],
                422
            );
        }

        // Store documents safely
        $documentPaths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $docFile) {
                $path = $docFile->store('verification_documents/' . $seller->id, 'public');
                $documentPaths[] = $path;
            }
        }

        $verificationData = [
            'seller_id' => $seller->id,
            'status' => 'pending',
            'documents' => $documentPaths,
            'business_description' => $request->input('business_description'),
            'reason_for_verification' => $request->input('reason_for_verification'),
            'rejection_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'submitted_at' => now(),
        ];

        $latestVerification = SellerVerification::where('seller_id', $seller->id)->first();

        if ($latestVerification) {
            $latestVerification->update($verificationData);
            $verification = $latestVerification->fresh();
        } else {
            $verification = SellerVerification::create($verificationData);
        }

        return ApiResponse::success(
            new SellerVerificationResource($verification),
            'Verification request submitted successfully. It will be reviewed by admin shortly.',
            [],
            201
        );
    }
}
