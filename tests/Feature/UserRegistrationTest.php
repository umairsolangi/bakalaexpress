<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_loads_successfully()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Create an Account');
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
        // Ensure extra fields are not in the form
        $response->assertDontSee('name="pickup_time"', false);
        $response->assertDontSee('name="address2"', false);
        $response->assertDontSee('name="zip"', false);
    }

    public function test_user_can_register_with_only_essential_info()
    {
        Mail::fake();

        $response = $this->post(route('register'), [
            'name' => 'Sara Khan',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verify.otp'));
        $this->assertDatabaseHas('users', [
            'name' => 'Sara Khan',
            'email' => 'sara@example.com',
            'sellerType' => 2,
            'mobile' => null,
            'address' => null,
            'city' => null,
            'zip' => null,
            'pickup_time' => null,
        ]);
    }

    public function test_registration_validates_required_fields()
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_registration_validates_password_confirmation()
    {
        $response = $this->post(route('register'), [
            'name' => 'Ali Raza',
            'email' => 'ali@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
