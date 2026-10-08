<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PartnerDeletionRequest;
use App\Services\Api\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerAccountController extends Controller
{
    public function __construct(
        protected PasswordResetService $resetService
    ) {}

    public function requestSellerDeletion(PartnerDeletionRequest $request): JsonResponse
    {
        return $this->resetService->requestPartnerDeletion(
            'seller',
            $request->user(),
            $request->password,
            $request->input('reason')
        );
    }

    public function requestRiderDeletion(PartnerDeletionRequest $request): JsonResponse
    {
        return $this->resetService->requestPartnerDeletion(
            'rider',
            $request->user(),
            $request->password,
            $request->input('reason')
        );
    }
}
