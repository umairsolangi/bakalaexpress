<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthSecurityService
{
    /**
     * Fixed bcrypt hash with cost 12 matching application hashing configuration.
     */
    public const DUMMY_BCRYPT_HASH = '$2y$12$.xvxE4BVnOOhPY9YJkTGv.0O9UykYPcybgKDZmkb1cC/DwYFy.auK';

    /**
     * Execute a constant-time dummy hash check against fixed bcrypt hash to prevent user enumeration timing attacks.
     */
    public function dummyHashCheck(?string $password = null): void
    {
        Hash::check($password ?? 'dummy-password-sample', self::DUMMY_BCRYPT_HASH);
    }

    /**
     * Determine if a login is locked either by (email + IP) 5-attempt limit OR (email) 20-attempt looser limit.
     */
    public static function isLoginLocked(string $role, string $email, string $ip): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);
        $emailHash = sha1($normalizedEmail);

        return Cache::has("api_{$role}_login_lock_{$ipHash}")
            || Cache::has("api_{$role}_login_global_lock_{$emailHash}");
    }

    /**
     * Record a failed login attempt for (email + IP) and global (email).
     */
    public static function recordFailedLogin(string $role, string $email, string $ip): void
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);
        $emailHash = sha1($normalizedEmail);

        // 1. Per (Email + IP) Counter: 5 attempts -> 15 min lock
        $ipAttemptsKey = "api_{$role}_login_attempts_{$ipHash}";
        $ipLockKey = "api_{$role}_login_lock_{$ipHash}";
        $ipAttempts = (int) Cache::get($ipAttemptsKey, 0) + 1;
        Cache::put($ipAttemptsKey, $ipAttempts, now()->addMinutes(15));

        if ($ipAttempts >= 5) {
            Cache::put($ipLockKey, true, now()->addMinutes(15));
        }

        // 2. Looser Global (Email) Counter: 20 attempts -> 15 min lock
        $globalAttemptsKey = "api_{$role}_login_global_attempts_{$emailHash}";
        $globalLockKey = "api_{$role}_login_global_lock_{$emailHash}";
        $globalAttempts = (int) Cache::get($globalAttemptsKey, 0) + 1;
        Cache::put($globalAttemptsKey, $globalAttempts, now()->addMinutes(15));

        if ($globalAttempts >= 20) {
            Cache::put($globalLockKey, true, now()->addMinutes(15));
        }
    }

    /**
     * Clear failed login attempts for this email + IP upon successful login.
     */
    public static function clearLoginAttempts(string $role, string $email, string $ip): void
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);

        Cache::forget("api_{$role}_login_attempts_{$ipHash}");
        Cache::forget("api_{$role}_login_lock_{$ipHash}");
    }

    /**
     * Determine if OTP verification is locked for (email + IP).
     */
    public static function isOtpLocked(string $email, string $ip): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);

        return Cache::has("api_otp_lock_{$ipHash}");
    }

    /**
     * Record a failed OTP verification attempt for (email + IP).
     */
    public static function recordFailedOtp(string $email, string $ip): void
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);
        $attemptsKey = "api_otp_attempts_{$ipHash}";
        $lockKey = "api_otp_lock_{$ipHash}";

        $attempts = (int) Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addMinutes(15));

        if ($attempts >= 5) {
            Cache::put($lockKey, true, now()->addMinutes(15));
        }
    }

    /**
     * Clear OTP attempts on successful verification.
     */
    public static function clearOtpAttempts(string $email, string $ip): void
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);

        Cache::forget("api_otp_attempts_{$ipHash}");
        Cache::forget("api_otp_lock_{$ipHash}");
    }

    /**
     * Determine if resend OTP is in cooldown for (email + IP).
     */
    public static function isResendOtpCooldown(string $email, string $ip): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);

        return Cache::has("api_otp_resend_cooldown_{$ipHash}");
    }

    /**
     * Set resend OTP 60-second cooldown for (email + IP).
     */
    public static function setResendOtpCooldown(string $email, string $ip): void
    {
        $normalizedEmail = strtolower(trim($email));
        $ipHash = sha1($normalizedEmail . '|' . $ip);

        Cache::put("api_otp_resend_cooldown_{$ipHash}", true, now()->addSeconds(60));
    }
}
