<?php

namespace Tests\Feature\Api\Auth;

use App\Services\Api\AuthSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DummyHashTimingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_customer_login_runs_dummy_hash_on_unknown_email(): void
    {
        $spy = $this->spy(AuthSecurityService::class);

        $response = $this->postJson('/api/v1/customer/auth/login', [
            'email' => 'unknown_customer@example.com',
            'password' => 'anyPassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
            ]);

        $spy->shouldHaveReceived('dummyHashCheck')->once();
    }

    public function test_seller_login_runs_dummy_hash_on_unknown_email(): void
    {
        $spy = $this->spy(AuthSecurityService::class);

        $response = $this->postJson('/api/v1/seller/auth/login', [
            'email' => 'unknown_seller@example.com',
            'password' => 'anyPassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
            ]);

        $spy->shouldHaveReceived('dummyHashCheck')->once();
    }

    public function test_rider_login_runs_dummy_hash_on_unknown_email(): void
    {
        $spy = $this->spy(AuthSecurityService::class);

        $response = $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'unknown_rider@example.com',
            'password' => 'anyPassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
            ]);

        $spy->shouldHaveReceived('dummyHashCheck')->once();
    }

    public function test_admin_login_runs_dummy_hash_on_unknown_email(): void
    {
        $spy = $this->spy(AuthSecurityService::class);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'unknown_admin@example.com',
            'password' => 'anyPassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
            ]);

        $spy->shouldHaveReceived('dummyHashCheck')->once();
    }

    public function test_customer_verify_otp_runs_dummy_hash_on_unknown_email(): void
    {
        $spy = $this->spy(AuthSecurityService::class);

        $response = $this->postJson('/api/v1/customer/auth/verify-otp', [
            'email' => 'unknown_otp@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_OTP',
            ]);

        $spy->shouldHaveReceived('dummyHashCheck')->once();
    }

    public function test_customer_resend_otp_runs_dummy_hash_on_unknown_email(): void
    {
        $spy = $this->spy(AuthSecurityService::class);

        $response = $this->postJson('/api/v1/customer/auth/resend-otp', [
            'email' => 'unknown_resend@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $spy->shouldHaveReceived('dummyHashCheck')->once();
    }
}
