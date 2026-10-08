<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_admin_can_login_successfully(): void
    {
        $admin = new User([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin12345'),
            'is_verified' => true,
        ]);
        $admin->sellerType = 1;
        $admin->save();

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin12345',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'name' => 'System Admin',
                        'email' => 'admin@example.com',
                        'sellerType' => 1,
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'sellerType'],
                ],
            ]);

        // Token must have ability: admin and 7-day expiry
        $tokenModel = $admin->tokens()->first();
        $this->assertNotNull($tokenModel);
        $this->assertTrue($tokenModel->can('admin'));
        $this->assertFalse($tokenModel->can('customer'));
        $this->assertFalse($tokenModel->can('seller'));
        $this->assertFalse($tokenModel->can('rider'));

        // Expires around 7 days from now
        $this->assertNotNull($tokenModel->expires_at);
        $this->assertTrue($tokenModel->expires_at->isFuture());
        $this->assertTrue($tokenModel->expires_at->diffInDays(now()) <= 7);
    }

    public function test_admin_login_fails_with_wrong_password(): void
    {
        $admin = new User([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_customer_cannot_login_through_admin_login(): void
    {
        $customer = new User([
            'name' => 'Normal Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('secret123'),
        ]);
        $customer->sellerType = 2; // Customer role
        $customer->save();

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'customer@example.com',
            'password' => 'secret123',
        ]);

        // Returns identical 401 INVALID_CREDENTIALS to prevent account/role probe
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_admin_login_locks_after_5_failed_attempts_on_ip_a_while_ip_b_can_still_login(): void
    {
        $admin = new User([
            'name' => 'Lock Admin',
            'email' => 'lockadmin@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        // 5 failed attempts from IP A (10.0.0.1)
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
                ->postJson('/api/v1/admin/auth/login', [
                    'email' => 'lockadmin@example.com',
                    'password' => 'wrong',
                ])->assertStatus(401);
        }

        // IP A is locked
        $responseA = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/v1/admin/auth/login', [
                'email' => 'lockadmin@example.com',
                'password' => 'admin12345',
            ]);

        $responseA->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);

        // IP B (10.0.0.2) can still log in
        $responseB = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->postJson('/api/v1/admin/auth/login', [
                'email' => 'lockadmin@example.com',
                'password' => 'admin12345',
            ]);

        $responseB->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ]);
    }

    public function test_admin_login_locks_after_20_failures_across_different_ips(): void
    {
        $admin = new User([
            'name' => 'Target Admin',
            'email' => 'targetadmin@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        for ($i = 1; $i <= 20; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.2.{$i}"])
                ->postJson('/api/v1/admin/auth/login', [
                    'email' => 'targetadmin@example.com',
                    'password' => 'wrong',
                ])->assertStatus(401);
        }

        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.2.99'])
            ->postJson('/api/v1/admin/auth/login', [
                'email' => 'targetadmin@example.com',
                'password' => 'admin12345',
            ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);
    }

    public function test_successful_admin_login_clears_ip_attempts(): void
    {
        $admin = new User([
            'name' => 'Reset Admin',
            'email' => 'resetadmin@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        for ($i = 0; $i < 4; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.8'])
                ->postJson('/api/v1/admin/auth/login', [
                    'email' => 'resetadmin@example.com',
                    'password' => 'wrong',
                ])->assertStatus(401);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.8'])
            ->postJson('/api/v1/admin/auth/login', [
                'email' => 'resetadmin@example.com',
                'password' => 'admin12345',
            ])->assertStatus(200);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.8'])
            ->postJson('/api/v1/admin/auth/login', [
                'email' => 'resetadmin@example.com',
                'password' => 'wrong',
            ])->assertStatus(401);
    }

    public function test_admin_me_endpoint_returns_profile(): void
    {
        $admin = new User([
            'name' => 'Me Admin',
            'email' => 'meadmin@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        $token = $admin->createToken('admin-api', ['admin'], now()->addDays(7))->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Me Admin',
                    'email' => 'meadmin@example.com',
                    'sellerType' => 1,
                ],
            ]);
    }

    public function test_admin_logout_deletes_current_token(): void
    {
        $admin = new User([
            'name' => 'Logout Admin',
            'email' => 'logoutadmin@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        $token = $admin->createToken('admin-api', ['admin'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/admin/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out.',
            ]);

        $this->assertEquals(0, $admin->tokens()->count());
    }

    public function test_demoted_admin_immediately_loses_access(): void
    {
        $admin = new User([
            'name' => 'Demoted Admin',
            'email' => 'demoted@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        $token = $admin->createToken('admin-api', ['admin'], now()->addDays(7))->plainTextToken;

        // Verify valid access before demotion
        $first = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/auth/me');
        $first->assertStatus(200);

        // Demote admin to regular user
        $admin->sellerType = 2;
        $admin->save();

        // Immediate next request with the same token must fail
        $second = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/auth/me');

        $second->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'FORBIDDEN_ROLE',
            ]);
    }

    public function test_admin_token_cannot_access_customer_routes(): void
    {
        $admin = new User([
            'name' => 'Boundary Admin',
            'email' => 'boundaryadmin@example.com',
            'password' => Hash::make('admin12345'),
        ]);
        $admin->sellerType = 1;
        $admin->save();

        $token = $admin->createToken('admin-api', ['admin'], now()->addDays(7))->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/customer/auth/me');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'FORBIDDEN_ROLE',
            ]);
    }
}
