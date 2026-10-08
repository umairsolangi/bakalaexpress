<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\CustomerAccountDeletionRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\Api\CustomerProfileResource;
use App\Models\DeviceToken;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProfileUpdate;
use App\Services\Api\AuthSecurityService;
use App\Services\Api\ApiResponse;
use App\Support\SanitizedImageUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerProfileController extends Controller
{
    /**
     * GET /api/v1/customer/profile
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        return ApiResponse::success(
            new CustomerProfileResource($customer),
            'Profile retrieved successfully.'
        );
    }

    /**
     * PUT /api/v1/customer/profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $validated = $request->validated();

        $updateFields = [];
        foreach (['name', 'mobile', 'address', 'address2', 'city', 'state', 'zip', 'sector', 'near_area'] as $field) {
            if ($request->has($field)) {
                $updateFields[$field] = $validated[$field] ?? null;
            }
        }

        // Email cannot be changed (follow web rules)
        // If avatar file is uploaded, process via SanitizedImageUpload
        if ($request->hasFile('profile_image')) {
            $path = SanitizedImageUpload::storeProfileImage($request->file('profile_image'), 'profile_images');

            $profileUpdate = UserProfileUpdate::firstOrNew(['user_id' => $customer->id]);
            if ($profileUpdate->profile_image && Storage::disk('public')->exists($profileUpdate->profile_image)) {
                Storage::disk('public')->delete($profileUpdate->profile_image);
            }
            $profileUpdate->profile_image = $path;
            $profileUpdate->save();
        }

        if (!empty($updateFields)) {
            $customer->forceFill($updateFields);
            $customer->save();
        }

        return ApiResponse::success(
            new CustomerProfileResource($customer->fresh()),
            'Profile updated successfully.'
        );
    }

    /**
     * POST /api/v1/customer/profile/password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $ip = $request->ip() ?: '127.0.0.1';
        $email = $customer->email;

        if (AuthSecurityService::isLoginLocked('customer', $email, $ip)) {
            return ApiResponse::error(
                'Too many failed attempts. Please try again in 15 minutes.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        if (!Hash::check($request->current_password, $customer->password)) {
            AuthSecurityService::recordFailedLogin('customer', $email, $ip);
            return ApiResponse::error('Current password does not match our records.', 'INVALID_PASSWORD', [], 422);
        }

        AuthSecurityService::clearLoginAttempts('customer', $email, $ip);

        $customer->password = Hash::make($request->password);
        $customer->save();

        // Revoke all other tokens and keep the current one
        $currentTokenId = $customer->currentAccessToken()?->id;
        if ($currentTokenId) {
            $customer->tokens()->where('id', '!=', $currentTokenId)->delete();
        }

        return ApiResponse::success(null, 'Password updated successfully. Other active sessions have been revoked.');
    }

    /**
     * DELETE /api/v1/customer/account
     */
    public function deleteAccount(CustomerAccountDeletionRequest $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        if (!Hash::check($request->password, $customer->password)) {
            return ApiResponse::error('Incorrect password.', 'INVALID_PASSWORD', [], 422);
        }

        $activeStatuses = config('bakala_orders.active_statuses', [
            'pending',
            'confirmed_by_seller',
            'preparing',
            'ready_for_pickup',
            'assigned_to_rider',
            'picked_up',
        ]);

        $hasActiveOrders = Order::where('user_id', $customer->id)
            ->whereIn('status', $activeStatuses)
            ->exists();

        if ($hasActiveOrders) {
            return ApiResponse::error(
                'Cannot delete account while you have active orders in progress.',
                'HAS_ACTIVE_ORDERS',
                [],
                422
            );
        }

        DB::transaction(function () use ($customer) {
            // Anonymize user row
            $randomSuffix = Str::lower(Str::random(8));
            $customer->forceFill([
                'name' => 'Deleted user',
                'email' => "deleted-{$customer->id}-{$randomSuffix}@deleted.invalid",
                'password' => Hash::make(Str::random(32)),
                'mobile' => null,
                'address' => null,
                'address2' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'sector' => null,
                'near_area' => null,
                'otp' => null,
                'otp_expires_at' => null,
                'is_verified' => false,
            ]);
            $customer->save();

            // Delete avatar
            $profileUpdate = UserProfileUpdate::where('user_id', $customer->id)->first();
            if ($profileUpdate) {
                if ($profileUpdate->profile_image && Storage::disk('public')->exists($profileUpdate->profile_image)) {
                    Storage::disk('public')->delete($profileUpdate->profile_image);
                }
                $profileUpdate->delete();
            }

            // Delete favorites
            Favorite::where('user_id', $customer->id)->delete();

            // Delete device tokens
            DeviceToken::where('tokenable_type', User::class)
                ->where('tokenable_id', $customer->id)
                ->delete();

            // Delete database notifications
            $customer->notifications()->delete();

            // Revoke all tokens
            $customer->tokens()->delete();
        });

        return ApiResponse::success(null, 'Your account has been deleted and anonymized successfully.');
    }
}
