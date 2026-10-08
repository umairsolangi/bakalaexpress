<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Services\Api\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $resetService
    ) {}

    public function forgotCustomer(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->resetService->forgotPassword('customer', $request->email, $request->ip() ?: '127.0.0.1');
    }

    public function resetCustomer(ResetPasswordRequest $request): JsonResponse
    {
        return $this->resetService->resetPassword('customer', $request->email, $request->otp, $request->password, $request->ip() ?: '127.0.0.1');
    }

    public function forgotSeller(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->resetService->forgotPassword('seller', $request->email, $request->ip() ?: '127.0.0.1');
    }

    public function resetSeller(ResetPasswordRequest $request): JsonResponse
    {
        return $this->resetService->resetPassword('seller', $request->email, $request->otp, $request->password, $request->ip() ?: '127.0.0.1');
    }

    public function forgotRider(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->resetService->forgotPassword('rider', $request->email, $request->ip() ?: '127.0.0.1');
    }

    public function resetRider(ResetPasswordRequest $request): JsonResponse
    {
        return $this->resetService->resetPassword('rider', $request->email, $request->otp, $request->password, $request->ip() ?: '127.0.0.1');
    }
}
