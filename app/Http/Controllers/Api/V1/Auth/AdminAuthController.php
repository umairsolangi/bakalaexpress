<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdminLoginRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\Api\ApiResponse;
use App\Services\Api\AuthSecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * Authenticate platform administrator.
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $ip = $request->ip() ?: '127.0.0.1';

        if (AuthSecurityService::isLoginLocked('admin', $email, $ip)) {
            return ApiResponse::error(
                'Too many failed login attempts. Please try again in 15 minutes.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            app(AuthSecurityService::class)->dummyHashCheck($request->password);
            AuthSecurityService::recordFailedLogin('admin', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        // Reject if wrong password or not an admin (sellerType != 1)
        if (! Hash::check($request->password, $user->password) || (int) ($user->sellerType ?? 0) !== 1) {
            AuthSecurityService::recordFailedLogin('admin', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        AuthSecurityService::clearLoginAttempts('admin', $email, $ip);

        // Admin tokens expire in 7 days
        $token = $user->createToken('admin-api', ['admin'], now()->addDays(7))->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login successful.');
    }

    /**
     * Admin profile endpoint.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()), 'Profile retrieved successfully.');
    }

    /**
     * Revoke current admin token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success((object) [], 'Successfully logged out.');
    }
}
