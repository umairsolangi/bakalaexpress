<?php

namespace Tests\Feature\Api\Seller;

use App\Models\Seller;
use App\Models\SellerVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->seller = Seller::create([
            'name' => 'Test Mart',
            'email' => 'mart@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'sector' => '4A',
            'is_open' => true,
            'accountIsApproved' => 1,
            'is_deleted' => 0,
        ]);
    }

    public function test_seller_verification_status_when_no_submission_exists(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $response = $this->getJson('/api/v1/seller/verification/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_verified' => false,
                    'has_submitted' => false,
                    'verification' => null,
                ],
            ]);
    }

    public function test_seller_can_submit_verification_request_with_documents(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $file1 = UploadedFile::fake()->create('trade_license.pdf', 500, 'application/pdf');
        $file2 = UploadedFile::fake()->image('shop_front.jpg');

        $response = $this->postJson('/api/v1/seller/verification/submit', [
            'business_description' => 'We are an established grocery retailer operating since 2015 in Sector 4A.',
            'reason_for_verification' => 'We want to provide verified authenticity and build customer trust on Bakala Express.',
            'documents' => [$file1, $file2],
            'verification_agreement' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'seller_id' => $this->seller->id,
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('seller_verifications', [
            'seller_id' => $this->seller->id,
            'status' => 'pending',
        ]);
    }

    public function test_seller_cannot_submit_duplicate_verification_if_already_pending(): void
    {
        SellerVerification::create([
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'documents' => ['doc1.pdf'],
            'business_description' => 'Existing description that is valid and detailed enough.',
            'reason_for_verification' => 'Existing reason for verification detailed enough.',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->seller, ['seller']);

        $file = UploadedFile::fake()->create('doc.pdf', 300, 'application/pdf');

        $response = $this->postJson('/api/v1/seller/verification/submit', [
            'business_description' => 'New business description for second attempt.',
            'reason_for_verification' => 'New reason for verification second attempt.',
            'documents' => [$file],
            'verification_agreement' => true,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VERIFICATION_ALREADY_EXISTS',
            ]);
    }

    public function test_seller_verification_status_returns_details_after_submission(): void
    {
        SellerVerification::create([
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'documents' => ['verification_documents/1/license.pdf'],
            'business_description' => 'Established grocery shop in Karachi Sector 4A.',
            'reason_for_verification' => 'Verified store badge request for authenticity.',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->seller, ['seller']);

        $response = $this->getJson('/api/v1/seller/verification/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'has_submitted' => true,
                    'verification' => [
                        'status' => 'pending',
                        'business_description' => 'Established grocery shop in Karachi Sector 4A.',
                    ],
                ],
            ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/seller/verification/status');
        $response->assertStatus(401);
    }
}
