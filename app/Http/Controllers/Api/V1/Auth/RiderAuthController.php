<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RiderLoginRequest;
use App\Http\Requests\Api\RiderRegisterRequest;
use App\Http\Resources\Api\RiderResource;
use App\Models\Rider;
use App\Services\Api\ApiResponse;
use App\Services\Api\AuthSecurityService;
use App\Support\Api\SanitizedPrivateImageUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RiderAuthController extends Controller
{
    /**
     * Register a new rider partner.
     */
    public function register(RiderRegisterRequest $request): JsonResponse
    {
        try {
            // Store all documents to private disk with sanitization and UUID naming
            $profileImagePath = SanitizedPrivateImageUpload::store(
                $request->file('profile_image'),
                'rider_docs/profile',
                'local'
            );
            $cnicFrontPath = SanitizedPrivateImageUpload::store(
                $request->file('cnic_front'),
                'rider_docs/cnic',
                'local'
            );
            $cnicBackPath = SanitizedPrivateImageUpload::store(
                $request->file('cnic_back'),
                'rider_docs/cnic',
                'local'
            );
            $licensePath = SanitizedPrivateImageUpload::store(
                $request->file('license_image'),
                'rider_docs/license',
                'local'
            );
            $vehicleImagePath = SanitizedPrivateImageUpload::store(
                $request->file('vehicle_image'),
                'rider_docs/vehicle',
                'local'
            );
            $regBookPath = $request->hasFile('registration_book')
                ? SanitizedPrivateImageUpload::store(
                    $request->file('registration_book'),
                    'rider_docs/vehicle',
                    'local'
                )
                : null;

            $rider = Rider::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'cnic_number' => $request->cnic_number,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'address' => $request->address,
                'profile_image' => $profileImagePath,
                'cnic_front' => $cnicFrontPath,
                'cnic_back' => $cnicBackPath,
                'license_image' => $licensePath,
                'vehicle_image' => $vehicleImagePath,
                'registration_book' => $regBookPath,
                'status' => 'offline',
                'is_approved' => false,
                'is_verified' => false, // Mirrors web RiderAuthController@register
            ]);

            // Sensitive fields (cnic_number, document paths) are excluded in registration response
            return ApiResponse::success([
                'rider' => new RiderResource($rider),
            ], 'Registration successful! Your account is pending admin approval.', [], 201);
        } catch (\Throwable $e) {
            Log::error('Rider API registration error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::error('Registration failed: ' . $e->getMessage(), 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * Authenticate rider partner account and issue Sanctum token.
     */
    public function login(RiderLoginRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $ip = $request->ip() ?: '127.0.0.1';

        if (AuthSecurityService::isLoginLocked('rider', $email, $ip)) {
            return ApiResponse::error(
                'Too many failed login attempts. Please try again in 15 minutes.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        $rider = Rider::where('email', $email)->first();

        if (! $rider) {
            app(AuthSecurityService::class)->dummyHashCheck($request->password);
            AuthSecurityService::recordFailedLogin('rider', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        if (! Hash::check($request->password, $rider->password)) {
            AuthSecurityService::recordFailedLogin('rider', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        AuthSecurityService::clearLoginAttempts('rider', $email, $ip);

        if (! $rider->is_approved) {
            return ApiResponse::error(
                'Your rider account is pending admin approval.',
                'PENDING_APPROVAL',
                [],
                403
            );
        }

        $token = $rider->createToken('rider-api', ['rider'], now()->addDays(30))->plainTextToken;

        // Login response does not expose cnic_number or document paths
        return ApiResponse::success([
            'token' => $token,
            'rider' => new RiderResource($rider),
        ], 'Login successful.');
    }

    /**
     * Rider profile endpoint (includes masked CNIC, zero document paths).
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            (new RiderResource($request->user()))->withSensitiveFields(true),
            'Profile retrieved successfully.'
        );
    }

    /**
     * Revoke current rider token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success((object) [], 'Successfully logged out.');
    }
}
