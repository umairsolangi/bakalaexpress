<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_seller_can_register_successfully(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('profile.jpg', 100, 100);

        $response = $this->postJson('/api/v1/seller/auth/register', [
            'name' => 'Kirana General Store',
            'email' => 'kirana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => $image,
            'city' => 'Karachi',
            'area' => 'Baldia Town',
            'sector' => '4A',
            'near_areas' => ['Ali Chowk', 'Main Bazaar'],
            'full_address' => 'Shop 5, Sector 4A, Baldia Town, Karachi',
            'terms' => 'on',
        ]);

        if (! function_exists('imagecreatefromstring')) {
            $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'Registration failed: Secure image processing requires the PHP GD extension.',
                ]);
            return;
        }

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful. Your account is waiting for admin approval.',
                'data' => [
                    'seller' => [
                        'name' => 'Kirana General Store',
                        'email' => 'kirana@example.com',
                        'accountIsApproved' => 0,
                    ],
                ],
            ]);

        // Must not return an auth token
        $this->assertArrayNotHasKey('token', $response->json('data'));

        // Check seller in database
        $seller = Seller::where('email', 'kirana@example.com')->first();
        $this->assertNotNull($seller);
        $this->assertEquals(0, $seller->accountIsApproved);
        $this->assertFalse((bool) $seller->is_deleted);
        $this->assertTrue(Hash::check('password123', $seller->password));
    }

    public function test_seller_register_validation_errors_return_422(): void
    {
        $response = $this->postJson('/api/v1/seller/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
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
                'errors' => ['name', 'email', 'password', 'profile_image', 'terms'],
            ]);
    }

    public function test_seller_register_rejects_mass_assignment_fields(): void
    {
        $response = $this->postJson('/api/v1/seller/auth/register', [
            'name' => 'Exploit Store',
            'email' => 'exploit@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accountIsApproved' => 1,
            'is_approved' => 1,
            'sellerType' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
            ]);
    }

    public function test_approved_seller_can_login(): void
    {
        $seller = Seller::create([
            'name' => 'Approved Seller',
            'email' => 'approved@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 1,
            'is_deleted' => 0,
            'is_open' => true,
            'opens_at' => '09:00:00',
            'closes_at' => '22:00:00',
        ]);

        $response = $this->postJson('/api/v1/seller/auth/login', [
            'email' => 'approved@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'seller' => [
                        'name' => 'Approved Seller',
                        'email' => 'approved@example.com',
                        'accountIsApproved' => 1,
                        'is_open' => true,
                        'opens_at' => '09:00:00',
                        'closes_at' => '22:00:00',
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'seller' => ['id', 'name', 'email', 'accountIsApproved', 'is_open'],
                ],
            ]);

        // Never expose password or remember token
        $this->assertArrayNotHasKey('password', $response->json('data.seller'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.seller'));

        // Check token ability
        $tokenModel = $seller->tokens()->first();
        $this->assertNotNull($tokenModel);
        $this->assertTrue($tokenModel->can('seller'));
        $this->assertFalse($tokenModel->can('customer'));
    }

    public function test_unapproved_seller_login_fails_with_403_pending_approval(): void
    {
        Seller::create([
            'name' => 'Pending Seller',
            'email' => 'pending@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 0,
            'is_deleted' => 0,
        ]);

        $response = $this->postJson('/api/v1/seller/auth/login', [
            'email' => 'pending@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'PENDING_APPROVAL',
                'message' => 'Your account is pending admin approval.',
            ]);
    }

    public function test_deleted_seller_login_fails_with_403_account_removed(): void
    {
        Seller::create([
            'name' => 'Deleted Seller',
            'email' => 'deleted@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 1,
            'is_deleted' => 1,
        ]);

        $response = $this->postJson('/api/v1/seller/auth/login', [
            'email' => 'deleted@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ACCOUNT_REMOVED',
                'message' => 'This merchant account has been deactivated.',
            ]);
    }

    public function test_seller_login_fails_with_wrong_password(): void
    {
        Seller::create([
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 1,
        ]);

        $response = $this->postJson('/api/v1/seller/auth/login', [
            'email' => 'seller@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_seller_login_fails_with_unknown_email_with_identical_message(): void
    {
        $response = $this->postJson('/api/v1/seller/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_seller_login_locks_after_5_failed_attempts(): void
    {
        Seller::create([
            'name' => 'Lock Seller',
            'email' => 'lock@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 1,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/seller/auth/login', [
                'email' => 'lock@example.com',
                'password' => 'wrong',
            ])->assertStatus(401);
        }

        $response = $this->postJson('/api/v1/seller/auth/login', [
            'email' => 'lock@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);
    }

    public function test_seller_me_endpoint_returns_profile(): void
    {
        $seller = Seller::create([
            'name' => 'Profile Seller',
            'email' => 'profile@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 1,
            'is_open' => true,
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
        ]);

        $token = $seller->createToken('seller-api', ['seller'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/seller/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Profile Seller',
                    'email' => 'profile@example.com',
                    'accountIsApproved' => 1,
                    'is_open' => true,
                    'opens_at' => '08:00:00',
                    'closes_at' => '23:00:00',
                ],
            ]);
    }

    public function test_seller_logout_deletes_current_token(): void
    {
        $seller = Seller::create([
            'name' => 'Logout Seller',
            'email' => 'logout@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 1,
        ]);

        $token = $seller->createToken('seller-api', ['seller'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/seller/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out.',
            ]);

        $this->assertEquals(0, $seller->tokens()->count());
    }

    public function test_seller_token_cannot_access_customer_routes(): void
    {
        $seller = Seller::create([
            'name' => 'Boundary Seller',
            'email' => 'boundary@example.com',
            'password' => 'secret123',
            'accountIsApproved' => 1,
        ]);

        $token = $seller->createToken('seller-api', ['seller'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/customer/auth/me');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'FORBIDDEN_ROLE',
            ]);
    }
}
