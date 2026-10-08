<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SellerLoginRequest;
use App\Http\Requests\Api\SellerRegisterRequest;
use App\Http\Resources\Api\SellerResource;
use App\Models\CatalogCategory;
use App\Models\Seller;
use App\Services\Api\ApiResponse;
use App\Services\Api\AuthSecurityService;
use App\Support\Api\SanitizedPrivateImageUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SellerAuthController extends Controller
{
    /**
     * Register a new merchant / seller.
     */
    public function register(SellerRegisterRequest $request): JsonResponse
    {
        try {
            $imagePath = null;
            if ($request->hasFile('profile_image')) {
                $imagePath = SanitizedPrivateImageUpload::store(
                    $request->file('profile_image'),
                    'profile_images',
                    'public'
                );
            }

            $defaultCategoryId = $request->catalog_category_id
                ?: CatalogCategory::where('is_active', true)->orderBy('name')->value('id');

            $seller = Seller::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password, // Handled by Seller model setPasswordAttribute
                'profile_image' => $imagePath,
                'city' => $request->input('city', 'Karachi'),
                'area' => $request->input('area', 'Baldia Town'),
                'sector' => $request->input('sector', '4A'),
                'catalog_category_id' => $defaultCategoryId,
                'near_areas' => array_values($request->near_areas ?? ['General Area']),
                'full_address' => $request->input('full_address', $request->input('area', 'Baldia Town') . ', Karachi'),
                'accountIsApproved' => 0,
            ]);

            return ApiResponse::success([
                'seller' => new SellerResource($seller),
            ], 'Registration successful. Your account is waiting for admin approval.', [], 201);
        } catch (\Throwable $e) {
            Log::error('Seller API registration error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::error('Registration failed: ' . $e->getMessage(), 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * Authenticate seller account and issue Sanctum token.
     */
    public function login(SellerLoginRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $ip = $request->ip() ?: '127.0.0.1';

        if (AuthSecurityService::isLoginLocked('seller', $email, $ip)) {
            return ApiResponse::error(
                'Too many failed login attempts. Please try again in 15 minutes.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        $seller = Seller::where('email', $email)->first();

        if (! $seller) {
            app(AuthSecurityService::class)->dummyHashCheck($request->password);
            AuthSecurityService::recordFailedLogin('seller', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        if (! Hash::check($request->password, $seller->password)) {
            AuthSecurityService::recordFailedLogin('seller', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        AuthSecurityService::clearLoginAttempts('seller', $email, $ip);

        if ((bool) $seller->is_deleted) {
            return ApiResponse::error('This merchant account has been deactivated.', 'ACCOUNT_REMOVED', [], 403);
        }

        if ((int) $seller->accountIsApproved !== 1) {
            return ApiResponse::error('Your account is pending admin approval.', 'PENDING_APPROVAL', [], 403);
        }

        $token = $seller->createToken('seller-api', ['seller'], now()->addDays(30))->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'seller' => new SellerResource($seller),
        ], 'Login successful.');
    }

    /**
     * Seller profile endpoint.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new SellerResource($request->user()), 'Profile retrieved successfully.');
    }

    /**
     * Revoke current seller token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success((object) [], 'Successfully logged out.');
    }
}
