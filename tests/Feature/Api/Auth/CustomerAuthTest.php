<?php

namespace Tests\Feature\Api\Auth;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_customer_can_register_successfully(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/customer/auth/register', [
            'name' => 'Sara Customer',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'mobile' => '03001234567',
            'city' => 'Karachi',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'Sara Customer',
                        'email' => 'sara@example.com',
                        'sellerType' => 2,
                        'is_verified' => false,
                    ],
                ],
            ]);

        // Must NOT return a token on registration
        $this->assertArrayNotHasKey('token', $response->json('data'));

        // Check user in database
        $user = User::where('email', 'sara@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(2, $user->sellerType);
        $this->assertFalse((bool) $user->is_verified);
        $this->assertEquals(6, strlen($user->otp));
        $this->assertNotNull($user->otp_expires_at);

        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->otp === $user->otp;
        });
    }

    public function test_registration_with_mail_failure_still_returns_201_success(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \Exception('SMTP connection timed out'));

        $response = $this->postJson('/api/v1/customer/auth/register', [
            'name' => 'Mail Fail User',
            'email' => 'mailfail@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertStringContainsString('resend-otp', $response->json('message'));
        $this->assertDatabaseHas('users', ['email' => 'mailfail@example.com']);
    }

    public function test_registration_validation_errors_return_standard_json_422(): void
    {
        $response = $this->postJson('/api/v1/customer/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'errors' => ['name', 'email', 'password'],
            ]);
    }

    public function test_registration_rejects_mass_assignment_fields(): void
    {
        $response = $this->postJson('/api/v1/customer/auth/register', [
            'name' => 'Hacker User',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sellerType' => 1,
            'is_verified' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
            ]);
    }

    public function test_customer_can_verify_otp_successfully(): void
    {
        $user = User::create([
            'name' => 'OTP User',
            'email' => 'otp@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 2,
            'is_verified' => false,
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/customer/auth/verify-otp', [
            'email' => 'otp@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Account verified successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'email', 'is_verified'],
                ],
            ]);

        $user->refresh();
        $this->assertTrue((bool) $user->is_verified);
        $this->assertNull($user->otp);
        $this->assertNull($user->otp_expires_at);

        // Verify token ability
        $plainToken = $response->json('data.token');
        $tokenModel = $user->tokens()->first();
        $this->assertNotNull($tokenModel);
        $this->assertTrue($tokenModel->can('customer'));
        $this->assertFalse($tokenModel->can('seller'));
    }

    public function test_verify_otp_fails_with_invalid_otp(): void
    {
        User::create([
            'name' => 'OTP User 2',
            'email' => 'otp2@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 2,
            'is_verified' => false,
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/customer/auth/verify-otp', [
            'email' => 'otp2@example.com',
            'otp' => '999999',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_OTP',
                'message' => 'Invalid OTP. Please try again.',
            ]);
    }

    public function test_verify_otp_locks_after_5_wrong_attempts_for_15_minutes(): void
    {
        User::create([
            'name' => 'OTP Lock User',
            'email' => 'otplock@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 2,
            'is_verified' => false,
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/customer/auth/verify-otp', [
                'email' => 'otplock@example.com',
                'otp' => '000000',
            ])->assertStatus(422);
        }

        // 6th attempt should be blocked by 15-minute lock
        $response = $this->postJson('/api/v1/customer/auth/verify-otp', [
            'email' => 'otplock@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);
    }

    public function test_verify_otp_fails_when_otp_is_expired(): void
    {
        User::create([
            'name' => 'Expired User',
            'email' => 'expired@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 2,
            'is_verified' => false,
            'otp' => '123456',
            'otp_expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson('/api/v1/customer/auth/verify-otp', [
            'email' => 'expired@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'OTP_EXPIRED',
                'message' => 'OTP has expired. Please request a new one.',
            ]);
    }

    public function test_resend_otp_generates_new_otp_and_sends_email(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Resend User',
            'email' => 'resend@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 2,
            'is_verified' => false,
            'otp' => '111111',
            'otp_expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson('/api/v1/customer/auth/resend-otp', [
            'email' => 'resend@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertNotEquals('111111', $user->otp);
        $this->assertTrue(now()->lt($user->otp_expires_at));

        Mail::assertSent(OtpMail::class);
    }

    public function test_resend_otp_throttles_within_60_seconds(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Throttle Resend User',
            'email' => 'throttleresend@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 2,
        ]);

        $first = $this->postJson('/api/v1/customer/auth/resend-otp', [
            'email' => 'throttleresend@example.com',
        ]);
        $first->assertStatus(200);

        // Immediate second attempt
        $second = $this->postJson('/api/v1/customer/auth/resend-otp', [
            'email' => 'throttleresend@example.com',
        ]);

        $second->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);
    }

    public function test_resend_otp_returns_generic_message_for_unknown_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/customer/auth/resend-otp', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'If this email is registered, a new verification code has been sent.',
            ]);

        Mail::assertNothingSent();
    }

    public function test_customer_login_success(): void
    {
        User::create([
            'name' => 'Verified Customer',
            'email' => 'verified@example.com',
            'password' => Hash::make('secret123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        $response = $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'verified@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'sellerType'],
                ],
            ]);

        // Sensitive fields should never be exposed
        $this->assertArrayNotHasKey('password', $response->json('data.user'));
        $this->assertArrayNotHasKey('otp', $response->json('data.user'));
        $this->assertArrayNotHasKey('otp_expires_at', $response->json('data.user'));
    }

    public function test_customer_login_fails_with_wrong_password(): void
    {
        User::create([
            'name' => 'Verified Customer',
            'email' => 'verified@example.com',
            'password' => Hash::make('secret123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        $response = $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'verified@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_customer_login_fails_with_unknown_email_with_identical_message(): void
    {
        $response = $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'anyPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_customer_login_locks_after_5_failed_attempts_on_ip_a_while_ip_b_can_still_login(): void
    {
        User::create([
            'name' => 'Login Lock Customer',
            'email' => 'loginlock@example.com',
            'password' => Hash::make('correct123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        // 5 failed attempts from IP A (10.0.0.1)
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
                ->postJson('/api/v1/customer/auth/login', [
                    'email' => 'loginlock@example.com',
                    'password' => 'badPassword',
                ])->assertStatus(401);
        }

        // IP A is now locked
        $responseA = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/v1/customer/auth/login', [
                'email' => 'loginlock@example.com',
                'password' => 'correct123',
            ]);

        $responseA->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);

        // IP B (10.0.0.2) is NOT locked and can successfully log in
        $responseB = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->postJson('/api/v1/customer/auth/login', [
                'email' => 'loginlock@example.com',
                'password' => 'correct123',
            ]);

        $responseB->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ]);
    }

    public function test_customer_login_locks_after_20_failures_spread_across_different_ips(): void
    {
        User::create([
            'name' => 'Distributed Attack Target',
            'email' => 'target@example.com',
            'password' => Hash::make('correct123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        // 20 failed attempts spread across 20 distinct IPs
        for ($i = 1; $i <= 20; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "192.168.1.{$i}"])
                ->postJson('/api/v1/customer/auth/login', [
                    'email' => 'target@example.com',
                    'password' => 'badPassword',
                ])->assertStatus(401);
        }

        // 21st attempt from a brand new IP (192.168.1.99) is locked by global email limit
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.99'])
            ->postJson('/api/v1/customer/auth/login', [
                'email' => 'target@example.com',
                'password' => 'correct123',
            ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);
    }

    public function test_successful_customer_login_clears_ip_counters(): void
    {
        User::create([
            'name' => 'Counter Reset Customer',
            'email' => 'reset@example.com',
            'password' => Hash::make('correct123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        // 4 failed attempts from IP A
        for ($i = 0; $i < 4; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
                ->postJson('/api/v1/customer/auth/login', [
                    'email' => 'reset@example.com',
                    'password' => 'badPassword',
                ])->assertStatus(401);
        }

        // Successful login resets counter for this email + IP
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->postJson('/api/v1/customer/auth/login', [
                'email' => 'reset@example.com',
                'password' => 'correct123',
            ])->assertStatus(200);

        // Another bad password should be attempt #1 again (returns 401, not locked 429)
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->postJson('/api/v1/customer/auth/login', [
                'email' => 'reset@example.com',
                'password' => 'badPassword',
            ])->assertStatus(401);
    }

    public function test_customer_login_rejects_unverified_account(): void
    {
        User::create([
            'name' => 'Unverified Customer',
            'email' => 'unverified@example.com',
            'password' => Hash::make('secret123'),
            'sellerType' => 2,
            'is_verified' => false,
        ]);

        $response = $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ACCOUNT_NOT_VERIFIED',
                'message' => 'Your account is not verified. Please verify your OTP.',
            ]);
    }

    public function test_admin_cannot_login_through_customer_login(): void
    {
        $admin = new User([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'is_verified' => true,
        ]);
        $admin->sellerType = 1;
        $admin->save();

        $response = $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        // Must reject with identical 401 INVALID_CREDENTIALS to prevent role probe
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_customer_me_endpoint_returns_profile(): void
    {
        $user = User::create([
            'name' => 'Me Customer',
            'email' => 'me@example.com',
            'password' => Hash::make('secret123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        $token = $user->createToken('customer-api', ['customer'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/customer/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Me Customer',
                    'email' => 'me@example.com',
                ],
            ]);
    }

    public function test_customer_logout_deletes_current_token(): void
    {
        $user = User::create([
            'name' => 'Logout Customer',
            'email' => 'logout@example.com',
            'password' => Hash::make('secret123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        $token1 = $user->createToken('token1', ['customer'])->plainTextToken;
        $token2 = $user->createToken('token2', ['customer'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->postJson('/api/v1/customer/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out.',
            ]);

        $this->assertEquals(1, $user->tokens()->count());
    }

    public function test_customer_token_cannot_access_seller_route(): void
    {
        $user = User::create([
            'name' => 'Role Boundary Customer',
            'email' => 'boundary@example.com',
            'password' => Hash::make('secret123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        $token = $user->createToken('customer-api', ['customer'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/seller/auth/me');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'FORBIDDEN_ROLE',
            ]);
    }

    public function test_unauthenticated_request_returns_401_json(): void
    {
        $response = $this->getJson('/api/v1/customer/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'Unauthenticated.',
            ]);
    }
}
