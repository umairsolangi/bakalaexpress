<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomerLoginRequest;
use App\Http\Requests\Api\CustomerRegisterRequest;
use App\Http\Requests\Api\ResendOtpRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\Api\UserResource;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\Api\ApiResponse;
use App\Services\Api\AuthSecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerAuthController extends Controller
{
    /**
     * Register a new customer account.
     */
    public function register(CustomerRegisterRequest $request): JsonResponse
    {
        try {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->input('mobile'),
                'address' => $request->input('address'),
                'address2' => $request->input('address2'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'zip' => $request->input('zip'),
                'pickup_time' => $request->input('pickup_time'),
                'is_verified' => false,
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            $user->sellerType = 2; // Customer / Buyer role
            $user->save();

            $emailSent = true;
            try {
                Mail::to($user->email)->send(new OtpMail($otp));
            } catch (\Throwable $e) {
                Log::error('Customer API registration OTP email failed: ' . $e->getMessage(), [
                    'email' => $user->email,
                    'exception' => $e,
                ]);
                $emailSent = false;
            }

            $message = $emailSent
                ? 'Registration successful. Please check your email for the verification OTP.'
                : 'Registration successful, but failed to send verification OTP email. Please use resend-otp.';

            return ApiResponse::success([
                'user' => new UserResource($user),
            ], $message, [], 201);
        } catch (\Throwable $e) {
            Log::error('Customer API registration error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::error('Registration failed: ' . $e->getMessage(), 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * Verify customer OTP and issue Sanctum token.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $ip = $request->ip() ?: '127.0.0.1';

        if (AuthSecurityService::isOtpLocked($email, $ip)) {
            return ApiResponse::error(
                'Too many failed OTP attempts. This email is locked for 15 minutes.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            app(AuthSecurityService::class)->dummyHashCheck($request->otp);
            AuthSecurityService::recordFailedOtp($email, $ip);
            return ApiResponse::error('Invalid OTP. Please try again.', 'INVALID_OTP', [], 422);
        }

        if (empty($user->otp) || empty($user->otp_expires_at) || Carbon::parse($user->otp_expires_at)->isPast()) {
            return ApiResponse::error('OTP has expired. Please request a new one.', 'OTP_EXPIRED', [], 422);
        }

        if (! hash_equals((string) $user->otp, (string) $request->otp)) {
            AuthSecurityService::recordFailedOtp($email, $ip);
            return ApiResponse::error('Invalid OTP. Please try again.', 'INVALID_OTP', [], 422);
        }

        AuthSecurityService::clearOtpAttempts($email, $ip);

        $user->is_verified = true;
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        // 30 days token expiry
        $token = $user->createToken('customer-api', ['customer'], now()->addDays(30))->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Account verified successfully.');
    }

    /**
     * Resend verification OTP to customer email.
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $ip = $request->ip() ?: '127.0.0.1';

        if (AuthSecurityService::isResendOtpCooldown($email, $ip)) {
            return ApiResponse::error(
                'Please wait 60 seconds before requesting another OTP.',
                'TOO_MANY_ATTEMPTS',
                [],
                429
            );
        }

        AuthSecurityService::setResendOtpCooldown($email, $ip);

        $user = User::where('email', $email)->first();

        // Only generate OTP if user exists and is a customer
        if ($user && (int) ($user->sellerType ?? 0) !== 1) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            try {
                Mail::to($user->email)->send(new OtpMail($otp));
            } catch (\Throwable $e) {
                Log::error('Customer API resend OTP email failed: ' . $e->getMessage(), [
                    'email' => $user->email,
                    'exception' => $e,
                ]);
            }
        } else {
            // Constant-time execution for non-existent users
            app(AuthSecurityService::class)->dummyHashCheck();
        }

        // Always return the same generic message whether user exists or not
        return ApiResponse::success(
            (object) [],
            'If this email is registered, a new verification code has been sent.'
        );
    }

    /**
     * Customer login endpoint.
     */
    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));
        $ip = $request->ip() ?: '127.0.0.1';

        if (AuthSecurityService::isLoginLocked('customer', $email, $ip)) {
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
            AuthSecurityService::recordFailedLogin('customer', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        // Admin accounts (sellerType = 1) must be rejected with the same invalid credentials message
        if (! Hash::check($request->password, $user->password) || (int) ($user->sellerType ?? 0) === 1) {
            AuthSecurityService::recordFailedLogin('customer', $email, $ip);
            return ApiResponse::error('These credentials do not match our records.', 'INVALID_CREDENTIALS', [], 401);
        }

        if (! $user->is_verified) {
            return ApiResponse::error(
                'Your account is not verified. Please verify your OTP.',
                'ACCOUNT_NOT_VERIFIED',
                [],
                403
            );
        }

        AuthSecurityService::clearLoginAttempts('customer', $email, $ip);

        $token = $user->createToken('customer-api', ['customer'], now()->addDays(30))->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login successful.');
    }

    /**
     * Customer logout endpoint (revokes current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success((object) [], 'Successfully logged out.');
    }

    /**
     * Customer profile endpoint.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()), 'Profile retrieved successfully.');
    }
}
