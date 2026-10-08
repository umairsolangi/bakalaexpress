<?php

namespace App\Services\Api;

use App\Mail\AccountDeletionRequestMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\User;
use App\Services\Api\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    /**
     * Send password reset OTP to user email.
     */
    public function forgotPassword(string $role, string $email, string $ip): JsonResponse
    {
        $normalizedEmail = strtolower(trim($email));
        $resendKey = "password_reset_resend:{$role}:{$normalizedEmail}:{$ip}";

        if (Cache::has($resendKey)) {
            return ApiResponse::error(
                'Please wait 60 seconds before requesting another OTP.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        $account = $this->findAccountByRole($role, $normalizedEmail);

        if ($account) {
            $otp = (string) random_int(100000, 999999);
            $hashedOtp = Hash::make($otp);

            $otpCacheKey = "password_reset_otp:{$role}:{$normalizedEmail}";
            Cache::put($otpCacheKey, $hashedOtp, now()->addMinutes(15));
            Cache::put($resendKey, true, now()->addSeconds(60));

            try {
                Mail::to($normalizedEmail)->send(new PasswordResetOtpMail($otp, $account->name));
            } catch (\Throwable $e) {
                Log::warning("Password reset email sending failed for {$role}: " . $e->getMessage());
            }
        } else {
            // Constant-time dummy hash verification
            Hash::check('000000', '$2y$10$e8wVf5rKzKkWt3rJk9O6UuY0Jq8W.2mCgQjQp.1iM.K3aB7T6L5vK');
            Cache::put($resendKey, true, now()->addSeconds(60));
        }

        return ApiResponse::success(
            null,
            'If your email is registered, you will receive a password reset OTP shortly.'
        );
    }

    /**
     * Reset password using OTP.
     */
    public function resetPassword(string $role, string $email, string $otp, string $newPassword, string $ip): JsonResponse
    {
        $normalizedEmail = strtolower(trim($email));
        $lockKey = "password_reset_locked:{$role}:{$normalizedEmail}:{$ip}";
        $attemptKey = "password_reset_attempts:{$role}:{$normalizedEmail}:{$ip}";
        $otpCacheKey = "password_reset_otp:{$role}:{$normalizedEmail}";

        if (Cache::has($lockKey)) {
            return ApiResponse::error(
                'Too many failed attempts. Please try again in 15 minutes.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        $account = $this->findAccountByRole($role, $normalizedEmail);
        $hashedOtp = Cache::get($otpCacheKey);

        if (!$account || ($role === 'customer' && !$account->is_verified)) {
            Hash::check('000000', '$2y$10$e8wVf5rKzKkWt3rJk9O6UuY0Jq8W.2mCgQjQp.1iM.K3aB7T6L5vK');
            return ApiResponse::error('Invalid or expired OTP. Please try again.', 'INVALID_OTP', [], 422);
        }

        if (!$hashedOtp) {
            return ApiResponse::error('Password reset OTP has expired. Please request a new one.', 'OTP_EXPIRED', [], 422);
        }

        if (!Hash::check($otp, $hashedOtp)) {
            $attempts = Cache::increment($attemptKey);
            if ($attempts === 1) {
                Cache::put($attemptKey, 1, now()->addMinutes(15));
            }
            if ($attempts >= 5) {
                Cache::put($lockKey, true, now()->addMinutes(15));
                Cache::forget($attemptKey);
            }

            return ApiResponse::error('Invalid OTP. Please try again.', 'INVALID_OTP', [], 422);
        }

        // Success: clear caches
        Cache::forget($otpCacheKey);
        Cache::forget($attemptKey);
        Cache::forget($lockKey);

        // Update password and revoke all tokens
        $account->password = Hash::make($newPassword);
        $account->save();
        $account->tokens()->delete();

        return ApiResponse::success(
            null,
            'Password has been reset successfully. You may now log in with your new password.'
        );
    }

    /**
     * Submit an account deletion request for a seller or rider partner.
     */
    public function requestPartnerDeletion(string $role, Model $account, string $password, ?string $reason): JsonResponse
    {
        if (!Hash::check($password, $account->password)) {
            return ApiResponse::error('Incorrect password.', 'INVALID_PASSWORD', [], 422);
        }

        $cacheKey = "deletion_request:{$role}:{$account->id}";
        if (Cache::has($cacheKey)) {
            return ApiResponse::error(
                'You have already submitted a deletion request in the last 24 hours.',
                'DELETION_REQUEST_PENDING',
                [],
                422
            );
        }

        $adminEmail = config('bakala_orders.admin_email', env('ADMIN_EMAIL', 'admin@bakalaexpress.com'));

        try {
            Mail::to($adminEmail)->send(new AccountDeletionRequestMail(
                role: $role,
                accountId: $account->id,
                accountName: $account->name,
                accountEmail: $account->email,
                reason: $reason
            ));
        } catch (\Throwable $e) {
            Log::warning("Partner account deletion request mail failed: " . $e->getMessage());
        }

        Cache::put($cacheKey, true, now()->addHours(24));

        return ApiResponse::success(
            null,
            'Your account deletion request has been submitted to administration for review.',
            [],
            202
        );
    }

    /**
     * Locate account model based on role.
     */
    protected function findAccountByRole(string $role, string $email): ?Model
    {
        return match ($role) {
            'customer' => User::where('email', $email)->first(),
            'seller' => Seller::where('email', $email)->where('is_deleted', false)->first(),
            'rider' => Rider::where('email', $email)->first(),
            default => null,
        };
    }
}
