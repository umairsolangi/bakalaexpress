<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Rider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiderAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_rider_can_register_successfully(): void
    {
        Storage::fake('local');

        $image = UploadedFile::fake()->image('doc.jpg', 100, 100);

        $response = $this->postJson('/api/v1/rider/auth/register', [
            'name' => 'Kashif Rider',
            'email' => 'kashif@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '03001234567', // 11 digits
            'cnic_number' => '4210112345671', // 13 digits
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'KHI-7890',
            'address' => 'Baldia Town, Karachi',
            'profile_image' => $image,
            'cnic_front' => $image,
            'cnic_back' => $image,
            'license_image' => $image,
            'vehicle_image' => $image,
            'registration_book' => $image,
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
                'message' => 'Registration successful! Your account is pending admin approval.',
                'data' => [
                    'rider' => [
                        'name' => 'Kashif Rider',
                        'email' => 'kashif@example.com',
                        'is_approved' => false,
                        'is_verified' => false,
                    ],
                ],
            ]);

        // Must NOT return a token
        $this->assertArrayNotHasKey('token', $response->json('data'));

        // Must NOT expose cnic_number or document paths in registration response
        $this->assertArrayNotHasKey('cnic_number', $response->json('data.rider'));
        $this->assertArrayNotHasKey('documents', $response->json('data.rider'));
        $this->assertArrayNotHasKey('cnic_front', $response->json('data.rider'));

        $rider = Rider::where('email', 'kashif@example.com')->first();
        $this->assertNotNull($rider);
        $this->assertEquals(0, $rider->is_approved);
        $this->assertEquals(0, $rider->is_verified);
        $this->assertEquals('4210112345671', $rider->cnic_number);

        // Verify files were stored to private local disk
        $this->assertNotNull($rider->cnic_front);
        Storage::disk('local')->assertExists($rider->cnic_front);
        Storage::disk('local')->assertExists($rider->license_image);
    }

    public function test_rider_register_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/rider/auth/register', [
            'name' => '',
            'email' => 'bad-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
            'phone' => '123', // Not 11 digits
            'cnic_number' => '12345', // Not 13 digits
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
                'errors' => ['name', 'email', 'password', 'phone', 'cnic_number', 'profile_image'],
            ]);
    }

    public function test_rider_register_rejects_mass_assignment(): void
    {
        $response = $this->postJson('/api/v1/rider/auth/register', [
            'name' => 'Rider Hack',
            'email' => 'hack@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_approved' => 1,
            'is_verified' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
            ]);
    }

    public function test_approved_rider_can_login(): void
    {
        $rider = Rider::create([
            'name' => 'Approved Rider',
            'email' => 'approvedrider@example.com',
            'password' => Hash::make('secret123'),
            'phone' => '03009999999',
            'cnic_number' => '4210199999991',
            'vehicle_number' => 'ABC-123',
            'vehicle_type' => 'bike',
            'address' => 'Karachi',
            'status' => 'offline',
            'is_approved' => 1,
            'is_verified' => 0,
        ]);

        $response = $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'approvedrider@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'rider' => [
                        'name' => 'Approved Rider',
                        'email' => 'approvedrider@example.com',
                        'is_approved' => true,
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'rider' => ['id', 'name', 'email', 'phone', 'is_approved'],
                ],
            ]);

        // Login response must not expose cnic_number or document paths
        $this->assertArrayNotHasKey('cnic_number', $response->json('data.rider'));
        $this->assertArrayNotHasKey('documents', $response->json('data.rider'));

        // Check token ability
        $tokenModel = $rider->tokens()->first();
        $this->assertNotNull($tokenModel);
        $this->assertTrue($tokenModel->can('rider'));
        $this->assertFalse($tokenModel->can('customer'));
        $this->assertFalse($tokenModel->can('seller'));
    }

    public function test_unapproved_rider_login_fails_with_403_pending_approval(): void
    {
        Rider::create([
            'name' => 'Pending Rider',
            'email' => 'pendingrider@example.com',
            'password' => Hash::make('secret123'),
            'phone' => '03001111111',
            'cnic_number' => '4210111111111',
            'vehicle_number' => 'XYZ-123',
            'vehicle_type' => 'bike',
            'address' => 'Karachi',
            'status' => 'offline',
            'is_approved' => 0,
        ]);

        $response = $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'pendingrider@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'PENDING_APPROVAL',
                'message' => 'Your rider account is pending admin approval.',
            ]);
    }

    public function test_rider_login_fails_with_wrong_password(): void
    {
        Rider::create([
            'name' => 'Test Rider',
            'email' => 'testrider@example.com',
            'password' => Hash::make('secret123'),
            'phone' => '03002222222',
            'cnic_number' => '4210122222221',
            'vehicle_number' => 'XYZ-456',
            'vehicle_type' => 'bike',
            'address' => 'Karachi',
            'status' => 'offline',
            'is_approved' => 1,
        ]);

        $response = $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'testrider@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'These credentials do not match our records.',
            ]);
    }

    public function test_rider_login_locks_after_5_failed_attempts(): void
    {
        Rider::create([
            'name' => 'Lock Rider',
            'email' => 'lockrider@example.com',
            'password' => Hash::make('secret123'),
            'phone' => '03003333333',
            'cnic_number' => '4210133333331',
            'vehicle_number' => 'XYZ-789',
            'vehicle_type' => 'bike',
            'address' => 'Karachi',
            'status' => 'offline',
            'is_approved' => 1,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/rider/auth/login', [
                'email' => 'lockrider@example.com',
                'password' => 'wrong',
            ])->assertStatus(401);
        }

        $response = $this->postJson('/api/v1/rider/auth/login', [
            'email' => 'lockrider@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
            ]);
    }

    public function test_rider_me_endpoint_returns_profile_with_sensitive_fields(): void
    {
        $rider = Rider::create([
            'name' => 'Me Rider',
            'email' => 'merider@example.com',
            'password' => Hash::make('secret123'),
            'phone' => '03004444444',
            'cnic_number' => '4210144444441',
            'vehicle_number' => 'XYZ-000',
            'vehicle_type' => 'bike',
            'address' => 'Karachi',
            'status' => 'offline',
            'is_approved' => 1,
            'cnic_front' => 'rider_docs/cnic/test_front.jpg',
        ]);

        $token = $rider->createToken('rider-api', ['rider'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/rider/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Me Rider',
                    'email' => 'merider@example.com',
                    'cnic_number' => '*****4441', // Masked CNIC format
                ],
            ]);

        // Documents block with storage paths must be completely removed
        $this->assertArrayNotHasKey('documents', $response->json('data'));
        $this->assertStringNotContainsString('4210144444441', $response->getContent());
        $this->assertStringNotContainsString('rider_docs', $response->getContent());
    }

    public function test_rider_logout_deletes_current_token(): void
    {
        $rider = Rider::create([
            'name' => 'Logout Rider',
            'email' => 'logoutrider@example.com',
            'password' => Hash::make('secret123'),
            'phone' => '03005555555',
            'cnic_number' => '4210155555551',
            'vehicle_number' => 'XYZ-111',
            'vehicle_type' => 'bike',
            'address' => 'Karachi',
            'status' => 'offline',
            'is_approved' => 1,
        ]);

        $token = $rider->createToken('rider-api', ['rider'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/rider/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out.',
            ]);

        $this->assertEquals(0, $rider->tokens()->count());
    }

    public function test_rider_token_cannot_access_seller_routes(): void
    {
        $rider = Rider::create([
            'name' => 'Boundary Rider',
            'email' => 'boundaryrider@example.com',
            'password' => Hash::make('secret123'),
            'phone' => '03006666666',
            'cnic_number' => '4210166666661',
            'vehicle_number' => 'XYZ-222',
            'vehicle_type' => 'bike',
            'address' => 'Karachi',
            'status' => 'offline',
            'is_approved' => 1,
        ]);

        $token = $rider->createToken('rider-api', ['rider'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/seller/auth/me');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'FORBIDDEN_ROLE',
            ]);
    }
}
