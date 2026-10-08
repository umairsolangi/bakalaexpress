<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Login error messages for unknown email and wrong password are identical.
     */
    public function test_login_returns_identical_error_message_for_unknown_email_and_wrong_password(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('correct_password'),
            'sellerType' => 2,
        ]);

        // Case A: Unknown email
        $unknownResponse = $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'some_password',
        ]);
        $unknownResponse->assertSessionHasErrors(['login' => 'These credentials do not match our records.']);

        // Case B: Existing user with wrong password
        $wrongPassResponse = $this->post(route('login'), [
            'email' => 'existing@example.com',
            'password' => 'wrong_password',
        ]);
        $wrongPassResponse->assertSessionHasErrors(['login' => 'These credentials do not match our records.']);
    }

    /**
     * Test 1b: Seller login error messages for unknown email and wrong password are identical.
     */
    public function test_seller_login_returns_identical_error_message_for_unknown_email_and_wrong_password(): void
    {
        Seller::create([
            'name' => 'Existing Seller',
            'email' => 'seller@example.com',
            'password' => Hash::make('correct_password'),
            'accountIsApproved' => 1,
            'is_deleted' => false,
        ]);

        // Case A: Unknown seller email
        $unknownResponse = $this->post(route('login.seller'), [
            'email' => 'unknownseller@example.com',
            'password' => 'some_password',
        ]);
        $unknownResponse->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

        // Case B: Existing seller with wrong password
        $wrongPassResponse = $this->post(route('login.seller'), [
            'email' => 'seller@example.com',
            'password' => 'wrong_password',
        ]);
        $wrongPassResponse->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
    }

    /**
     * Test 2: Throttle middleware enforces rate limit on /verify-otp (6th request within 1 min returns 429).
     */
    public function test_verify_otp_throttle_returns_429_on_sixth_request(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('verify.otp'), [
                'email' => 'throttletest@example.com',
                'otp' => '000000',
            ]);
        }

        $sixthResponse = $this->post(route('verify.otp'), [
            'email' => 'throttletest@example.com',
            'otp' => '000000',
        ]);

        $sixthResponse->assertStatus(429);
    }

    /**
     * Test 3a: Chat getMessages with "since" returns only newer messages.
     */
    public function test_chat_get_messages_with_since_returns_only_newer_messages(): void
    {
        $user = User::create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'sellerType' => 2,
        ]);

        $seller = Seller::create([
            'name' => 'Store',
            'email' => 'store@example.com',
            'password' => Hash::make('password'),
            'accountIsApproved' => 1,
            'is_deleted' => false,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'seller_id' => $seller->id,
            'address' => '123 Main St',
            'phone' => '03001234567',
            'total_amount' => 500,
            'status' => 'pending',
        ]);

        $msg1 = Message::create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
            'sender_type' => 'user',
            'message' => 'First message',
            'is_read' => false,
        ]);

        $msg2 = Message::create([
            'order_id' => $order->id,
            'sender_id' => $seller->id,
            'sender_type' => 'seller',
            'message' => 'Second message',
            'is_read' => false,
        ]);

        $msg3 = Message::create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
            'sender_type' => 'user',
            'message' => 'Third message',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->getJson(route('chat.get-messages', [
            'order' => $order->id,
            'since' => $msg1->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $messages = $response->json('messages');
        $this->assertCount(2, $messages);
        $this->assertEquals($msg2->id, $messages[0]['id']);
        $this->assertEquals($msg3->id, $messages[1]['id']);
    }

    /**
     * Test 3b: Chat getMessages unauthorized user gets 403.
     */
    public function test_chat_get_messages_unauthorized_user_forbidden(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'sellerType' => 2,
        ]);

        $unauthorizedUser = User::create([
            'name' => 'Stranger',
            'email' => 'stranger@example.com',
            'password' => Hash::make('password'),
            'sellerType' => 2,
        ]);

        $seller = Seller::create([
            'name' => 'Shop',
            'email' => 'shop@example.com',
            'password' => Hash::make('password'),
            'accountIsApproved' => 1,
            'is_deleted' => false,
        ]);

        $order = Order::create([
            'user_id' => $owner->id,
            'seller_id' => $seller->id,
            'address' => '123 Main St',
            'phone' => '03001234567',
            'total_amount' => 300,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($unauthorizedUser)->getJson(route('chat.get-messages', $order->id));
        $response->assertStatus(403);
    }

    /**
     * Test 4: Home page pagination works and preserves filter parameters.
     */
    public function test_homepage_pagination_works_and_preserves_filter_parameters(): void
    {
        $authUser = User::create([
            'name' => 'Home Visitor',
            'email' => 'visitor@example.com',
            'password' => Hash::make('password'),
            'sellerType' => 2,
        ]);

        for ($i = 1; $i <= 25; $i++) {
            Seller::create([
                'name' => "Seller {$i}",
                'email' => "seller{$i}@example.com",
                'password' => Hash::make('password'),
                'accountIsApproved' => 1,
                'is_deleted' => false,
                'is_open' => true,
                'sector' => '4A',
            ]);
        }

        $response = $this->actingAs($authUser)->get(route('home', [
            'sector' => '4A',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        // Pagination link preserves query string
        $response->assertSee('sector=4A');
    }

    /**
     * Test 5: The OTP value is never written to log when requesting an OTP.
     */
    public function test_otp_value_is_never_logged_during_registration(): void
    {
        Mail::fake();
        Log::spy();

        $response = $this->post(route('register'), [
            'name' => 'Secret Test User',
            'email' => 'secretotp@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('verify.otp'));

        $user = User::where('email', 'secretotp@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->otp);

        $otp = $user->otp;

        // Ensure Log::info never logged the OTP value or string containing it
        Log::shouldNotHaveReceived('info', function ($message) use ($otp) {
            if (is_string($message) && str_contains($message, (string) $otp)) {
                return true;
            }
            if (is_array($message) && in_array($otp, $message, true)) {
                return true;
            }
            return false;
        });
    }

    /**
     * Test 6: Verify key pages load successfully under simulated production environment (APP_ENV=production, APP_DEBUG=false).
     */
    public function test_main_pages_load_successfully_in_production_mode(): void
    {
        config(['app.env' => 'production', 'app.debug' => false]);

        $user = User::create([
            'name' => 'Prod Tester',
            'email' => 'prodtester@example.com',
            'password' => Hash::make('password'),
            'sellerType' => 2,
        ]);

        $seller = Seller::create([
            'name' => 'Prod Store',
            'email' => 'prodstore@example.com',
            'password' => Hash::make('password'),
            'accountIsApproved' => 1,
            'is_deleted' => false,
            'is_open' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'seller_id' => $seller->id,
            'address' => '456 Market St',
            'phone' => '03009876543',
            'total_amount' => 1000,
            'status' => 'pending',
        ]);

        // Public pages
        $publicRoutes = [
            '/',
            '/login',
            '/register',
            '/seller/login',
            '/seller/register',
            '/rider/login',
            '/rider/register',
            '/cart',
        ];

        foreach ($publicRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertSuccessful();
        }

        // Authenticated order flow and chat pages
        $authRoutes = [
            '/order/history',
            '/orders',
            "/order/{$order->id}",
            "/chat/{$order->id}",
        ];

        foreach ($authRoutes as $uri) {
            $response = $this->actingAs($user)->get($uri);
            $response->assertSuccessful();
        }

        // Checkout redirects to cart view when cart is empty
        $checkoutResponse = $this->actingAs($user)->get('/checkout');
        $checkoutResponse->assertRedirect(route('cart.view'));
    }
}
