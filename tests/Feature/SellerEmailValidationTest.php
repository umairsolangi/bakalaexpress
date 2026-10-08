<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Seller;

class SellerEmailValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    /**
     * Test registration with invalid email formats.
     *
     * @return void
     */
    public function testRegistrationWithInvalidEmails()
    {
        // Test missing @ symbol
        $response = $this->post(route('register.seller'), [
            'name' => 'Test Seller',
            'email' => 'invalidemail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => $this->createTestImage(),
            'city' => 'Test City',
            'area' => 'Test Area',
            'terms' => 'on',
        ]);
        
        $response->assertSessionHasErrors('email');
        
        // Test email with multiple @ symbols
        $response = $this->post(route('register.seller'), [
            'name' => 'Test Seller',
            'email' => 'invalid@email@domain.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => $this->createTestImage(),
            'city' => 'Test City',
            'area' => 'Test Area',
            'terms' => 'on',
        ]);
        
        $response->assertSessionHasErrors('email');
        
        // Test email with domain but no TLD
        $response = $this->post(route('register.seller'), [
            'name' => 'Test Seller',
            'email' => 'invalid@domainnotld',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => $this->createTestImage(),
            'city' => 'Test City',
            'area' => 'Test Area',
            'terms' => 'on',
        ]);
        
        $response->assertSessionHasErrors('email');
        
        // Test with suspicious domain
        $response = $this->post(route('register.seller'), [
            'name' => 'Test Seller',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => $this->createTestImage(),
            'city' => 'Test City',
            'area' => 'Test Area',
            'terms' => 'on',
        ]);
        
        $response->assertSessionHasErrors('email');
    }
    
    /**
     * Test login with invalid email formats.
     *
     * @return void
     */
    public function testLoginWithInvalidEmails()
    {
        // Test missing @ symbol
        $response = $this->post(route('login.seller'), [
            'email' => 'invalidemail.com',
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
        
        // Test email with multiple @ symbols
        $response = $this->post(route('login.seller'), [
            'email' => 'invalid@email@domain.com',
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
    }
    
    /**
     * Test registration with valid email formats.
     *
     * @return void
     */
    public function testRegistrationWithValidEmails()
    {
        // Test simple valid email
        $response = $this->post(route('register.seller'), [
            'name' => 'Test Seller',
            'email' => 'valid@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => $this->createTestImage(),
            'city' => 'Test City',
            'area' => 'Test Area',
            'terms' => 'on',
        ]);
        
        // Should redirect to success page
        $response->assertRedirect(route('register.seller', ['registration' => 'success']));
        
        // Test valid email with subdomain
        $response = $this->post(route('register.seller'), [
            'name' => 'Test Seller 2',
            'email' => 'valid@sub.domain.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => $this->createTestImage(),
            'city' => 'Test City',
            'area' => 'Test Area',
            'terms' => 'on',
        ]);
        
        // Should redirect to success page
        $response->assertRedirect(route('register.seller', ['registration' => 'success']));
    }
    
    /**
     * Helper method to create a test image for form submission.
     *
     * @return \Illuminate\Http\UploadedFile
     */
    private function createTestImage()
    {
        return \Illuminate\Http\UploadedFile::fake()->create('profile.jpg', 100, 'image/jpeg');
    }
} 