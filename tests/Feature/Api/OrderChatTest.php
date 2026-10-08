<?php

namespace Tests\Feature\Api;

use App\Models\CatalogCategory;
use App\Models\DeviceToken;
use App\Models\Message;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $customer2;
    protected Seller $seller;
    protected Seller $seller2;
    protected Rider $rider;
    protected Order $order;
    protected CatalogCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('order_chat_send:*');

        $this->category = CatalogCategory::create([
            'name' => 'Groceries',
            'slug' => 'groceries',
            'is_active' => true,
        ]);

        $this->seller = Seller::create([
            'name' => 'Al-Madina Mart',
            'email' => 'seller1@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'area' => 'Gulshan',
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
            'is_open' => true,
            'catalog_category_id' => $this->category->id,
            'accountIsApproved' => 1,
            'is_deleted' => 0,
        ]);

        $this->seller2 = Seller::create([
            'name' => 'Bismillah Store',
            'email' => 'seller2@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'area' => 'Gulshan',
            'accountIsApproved' => 1,
            'is_deleted' => 0,
        ]);

        $this->customer = User::create([
            'name' => 'Fatima Noor',
            'email' => 'fatima@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03009998877',
            'city' => 'Karachi',
            'sellerType' => 0,
            'is_verified' => true,
        ]);

        $this->customer2 = User::create([
            'name' => 'Zainab Ahmed',
            'email' => 'zainab@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03001112233',
            'city' => 'Karachi',
            'sellerType' => 0,
            'is_verified' => true,
        ]);

        $this->rider = Rider::create([
            'name' => 'Hamza Rider',
            'email' => 'hamza@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03120000000',
            'cnic_number' => '42101-9999999-1',
            'vehicle_type' => 'Motorcycle',
            'vehicle_number' => 'KHI-9999',
            'address' => 'Gulshan, Karachi',
            'status' => 'online',
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $this->order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House 123, Street 4, Karachi',
            'phone' => '03009998877',
            'status' => 'confirmed_by_seller',
            'total_amount' => 500.00,
        ]);
    }

    public function test_both_directions_work_and_ownership_enforced(): void
    {
        // 1. Customer sends message
        Sanctum::actingAs($this->customer, ['customer']);
        $custSend = $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [
            'message' => 'Hello seller, please deliver soon.',
        ]);

        $custSend->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mine', true)
            ->assertJsonPath('data.sender_name', 'Fatima')
            ->assertJsonPath('data.message', 'Hello seller, please deliver soon.')
            ->assertJsonPath('data.is_read', false);

        // 2. Seller sends reply
        Sanctum::actingAs($this->seller, ['seller']);
        $sellerSend = $this->postJson("/api/v1/seller/orders/{$this->order->id}/messages", [
            'message' => 'Sure! We are preparing your order.',
        ]);

        $sellerSend->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mine', true)
            ->assertJsonPath('data.sender_name', 'Al-Madina Mart')
            ->assertJsonPath('data.message', 'Sure! We are preparing your order.');

        // 3. Customer views messages
        Sanctum::actingAs($this->customer, ['customer']);
        $custGet = $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages");
        $custGet->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.mine', true)
            ->assertJsonPath('data.0.sender_name', 'Fatima')
            ->assertJsonPath('data.1.mine', false)
            ->assertJsonPath('data.1.sender_name', 'Al-Madina Mart');

        // 4. Seller views messages
        Sanctum::actingAs($this->seller, ['seller']);
        $sellerGet = $this->getJson("/api/v1/seller/orders/{$this->order->id}/messages");
        $sellerGet->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.mine', false)
            ->assertJsonPath('data.0.sender_name', 'Fatima')
            ->assertJsonPath('data.1.mine', true)
            ->assertJsonPath('data.1.sender_name', 'Al-Madina Mart');

        // 5. Ownership 404 for other customer
        Sanctum::actingAs($this->customer2, ['customer']);
        $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages")->assertStatus(404);
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", ['message' => 'intruder'])->assertStatus(404);

        // 6. Ownership 404 for other seller
        Sanctum::actingAs($this->seller2, ['seller']);
        $this->getJson("/api/v1/seller/orders/{$this->order->id}/messages")->assertStatus(404);
        $this->postJson("/api/v1/seller/orders/{$this->order->id}/messages", ['message' => 'intruder'])->assertStatus(404);

        // 7. Rider token gets 403
        Sanctum::actingAs($this->rider, ['rider']);
        $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages")->assertStatus(403);
        $this->getJson("/api/v1/seller/orders/{$this->order->id}/messages")->assertStatus(403);

        // 8. Unauthenticated gets 401
        $this->app['auth']->forgetGuards();
        $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages")->assertStatus(401);
    }

    public function test_since_id_returns_only_newer_messages_and_limit_bounds(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        for ($i = 1; $i <= 10; $i++) {
            Message::create([
                'order_id' => $this->order->id,
                'sender_id' => $this->customer->id,
                'sender_type' => 'user',
                'message' => "Message {$i}",
                'is_read' => false,
            ]);
        }

        // Without since_id and limit 5: returns latest 5 messages, oldest first (6, 7, 8, 9, 10)
        $res = $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages?limit=5");
        $res->assertStatus(200)
            ->assertJsonCount(5, 'data');
        $data = $res->json('data');
        $this->assertEquals('Message 6', $data[0]['message']);
        $this->assertEquals('Message 10', $data[4]['message']);
        $this->assertTrue($data[0]['id'] < $data[4]['id']); // chronological / oldest first

        // With since_id = 7: returns messages 8, 9, 10
        $since7 = $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages?since_id=7");
        $since7->assertStatus(200)
            ->assertJsonCount(3, 'data');
        $dataSince = $since7->json('data');
        $this->assertEquals('Message 8', $dataSince[0]['message']);
        $this->assertEquals('Message 10', $dataSince[2]['message']);

        // Limit capped to 50 max
        $maxLimit = $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages?limit=999");
        $maxLimit->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }

    public function test_mark_as_read_marks_only_other_party_messages(): void
    {
        // Customer creates msg 1
        $msg1 = Message::create([
            'order_id' => $this->order->id,
            'sender_id' => $this->customer->id,
            'sender_type' => 'user',
            'message' => 'From customer',
            'is_read' => false,
        ]);

        // Seller creates msg 2 & 3
        $msg2 = Message::create([
            'order_id' => $this->order->id,
            'sender_id' => $this->seller->id,
            'sender_type' => 'seller',
            'message' => 'From seller 1',
            'is_read' => false,
        ]);
        $msg3 = Message::create([
            'order_id' => $this->order->id,
            'sender_id' => $this->seller->id,
            'sender_type' => 'seller',
            'message' => 'From seller 2',
            'is_read' => false,
        ]);

        // Customer sees 2 unread messages from seller
        Sanctum::actingAs($this->customer, ['customer']);
        $getRes = $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages");
        $getRes->assertStatus(200)
            ->assertJsonPath('meta.unread_count', 2);

        // Customer marks as read
        $readRes = $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages/read");
        $readRes->assertStatus(200)
            ->assertJsonPath('data.unread_count', 0);

        // Seller messages 2 & 3 are now read
        $this->assertTrue((bool) $msg2->fresh()->is_read);
        $this->assertTrue((bool) $msg3->fresh()->is_read);
        // Customer own message 1 remains unread (seller hasn't read it yet)
        $this->assertFalse((bool) $msg1->fresh()->is_read);

        // Seller sees 1 unread message from customer
        Sanctum::actingAs($this->seller, ['seller']);
        $sellerGet = $this->getJson("/api/v1/seller/orders/{$this->order->id}/messages");
        $sellerGet->assertStatus(200)
            ->assertJsonPath('meta.unread_count', 1);

        // Seller marks as read
        $sellerRead = $this->postJson("/api/v1/seller/orders/{$this->order->id}/messages/read");
        $sellerRead->assertStatus(200)
            ->assertJsonPath('data.unread_count', 0);

        $this->assertTrue((bool) $msg1->fresh()->is_read);
    }

    public function test_messages_created_by_api_and_web_are_mutually_visible(): void
    {
        // Web creates message as user
        Message::create([
            'order_id' => $this->order->id,
            'sender_id' => $this->customer->id,
            'sender_type' => 'user',
            'message' => 'Created via web form',
            'is_read' => false,
        ]);

        // API creates message as seller
        Sanctum::actingAs($this->seller, ['seller']);
        $this->postJson("/api/v1/seller/orders/{$this->order->id}/messages", [
            'message' => 'Created via mobile API',
        ])->assertStatus(200);

        // Web can read both messages from database
        $all = Message::where('order_id', $this->order->id)->orderBy('id', 'asc')->get();
        $this->assertCount(2, $all);
        $this->assertEquals('user', $all[0]->sender_type);
        $this->assertEquals('seller', $all[1]->sender_type);

        // API customer sees both
        Sanctum::actingAs($this->customer, ['customer']);
        $apiGet = $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages");
        $apiGet->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_validation_empty_spaces_over_limit_and_html_stripped(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        // Empty body
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');

        // Only whitespace
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", ['message' => '     '])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');

        // Only HTML tags
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", ['message' => '<div><b></b></div>'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');

        // Over 1000 characters
        $long = str_repeat('a', 1001);
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", ['message' => $long])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');

        // HTML tags stripped
        $htmlMsg = $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [
            'message' => '<p>Hello <strong>Merchant</strong> <script>alert("xss")</script></p>',
        ]);
        $htmlMsg->assertStatus(200);
        $this->assertStringNotContainsString('<p>', $htmlMsg->json('data.message'));
        $this->assertStringNotContainsString('<strong>', $htmlMsg->json('data.message'));
        $this->assertStringNotContainsString('<script>', $htmlMsg->json('data.message'));
    }

    public function test_chat_closed_rule_for_terminal_orders(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        // Active order allows chat
        $this->order->status = 'preparing';
        $this->order->save();
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", ['message' => 'Active ok'])
            ->assertStatus(200);

        // Terminal order within 48h allows chat
        $this->order->status = 'delivered';
        $this->order->updated_at = now()->subHours(2);
        $this->order->save();
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", ['message' => 'Delivered 2h ago ok'])
            ->assertStatus(200);

        // Terminal order after 48h (e.g. 50 hours ago) rejects with CHAT_CLOSED
        $this->order->updated_at = now()->subHours(50);
        $this->order->save();
        $res = $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", ['message' => 'Too late']);
        $res->assertStatus(422)
            ->assertJsonPath('code', 'CHAT_CLOSED');

        // GET on closed order works, but suggested_poll_seconds is 30
        $getRes = $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages");
        $getRes->assertStatus(200)
            ->assertJsonPath('meta.suggested_poll_seconds', 30);
    }

    public function test_throttle_20_per_minute_returns_429(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        RateLimiter::clear("order_chat_send:customer:{$this->customer->id}:{$this->order->id}");

        for ($i = 1; $i <= 20; $i++) {
            $res = $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [
                'message' => "Message {$i}",
            ]);
            $this->assertEquals(200, $res->status());
        }

        // 21st request hits 429
        $res21 = $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [
            'message' => 'Exceeding limit',
        ]);
        $res21->assertStatus(429)
            ->assertJsonPath('code', 'RATE_LIMIT_EXCEEDED');
    }

    public function test_push_sent_to_other_party_with_preview_and_expo_error_tolerance(): void
    {
        Config::set('push.enabled', true);
        Config::set('push.chat_preview', true);

        // Register token for seller
        DeviceToken::create([
            'tokenable_type' => Seller::class,
            'tokenable_id' => $this->seller->id,
            'token' => 'ExponentPushToken[seller_expo_token_123]',
            'platform' => 'android',
            'last_seen_at' => now(),
        ]);

        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    ['status' => 'ok', 'id' => 'ticket-1'],
                ],
            ], 200),
        ]);

        // Customer sends message
        Sanctum::actingAs($this->customer, ['customer']);
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [
            'message' => 'Please add extra bag',
        ])->assertStatus(200);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $msg = $body[0] ?? [];
            return $msg['to'] === 'ExponentPushToken[seller_expo_token_123]'
                && $msg['title'] === "New message, order #{$this->order->id}"
                && $msg['body'] === 'Please add extra bag'
                && ($msg['data']['type'] ?? '') === 'chat_message';
        });

        // Test preview turned off
        Config::set('push.chat_preview', false);
        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[customer_expo_token_456]',
            'platform' => 'ios',
            'last_seen_at' => now(),
        ]);

        Sanctum::actingAs($this->seller, ['seller']);
        $this->postJson("/api/v1/seller/orders/{$this->order->id}/messages", [
            'message' => 'Added your bag',
        ])->assertStatus(200);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $msg = $body[0] ?? [];
            return $msg['to'] === 'ExponentPushToken[customer_expo_token_456]'
                && $msg['body'] === 'You have a new message';
        });

        // Test Expo 500 error does NOT fail message send
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['error' => 'Server error'], 500),
        ]);

        Sanctum::actingAs($this->customer, ['customer']);
        $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [
            'message' => 'Another message with Expo failing',
        ])->assertStatus(200);
    }

    public function test_unread_messages_appears_in_order_resources_and_query_count_bounded(): void
    {
        // Create an unread message from seller to customer
        Message::create([
            'order_id' => $this->order->id,
            'sender_id' => $this->seller->id,
            'sender_type' => 'seller',
            'message' => 'Unread for customer',
            'is_read' => false,
        ]);

        // Customer order show has unread_messages = 1
        Sanctum::actingAs($this->customer, ['customer']);
        $custShow = $this->getJson("/api/v1/customer/orders/{$this->order->id}");
        $custShow->assertStatus(200)
            ->assertJsonPath('data.unread_messages', 1);

        // Customer active orders list has unread_messages = 1
        $custActive = $this->getJson('/api/v1/customer/orders/active');
        $custActive->assertStatus(200)
            ->assertJsonPath('data.0.unread_messages', 1);

        // Seller order show has unread_messages = 0 (since message was sent by seller)
        Sanctum::actingAs($this->seller, ['seller']);
        $sellerShow = $this->getJson("/api/v1/seller/orders/{$this->order->id}");
        $sellerShow->assertStatus(200)
            ->assertJsonPath('data.unread_messages', 0);

        // Seller orders list has unread_messages = 0
        $sellerOrders = $this->getJson('/api/v1/seller/orders');
        $sellerOrders->assertStatus(200)
            ->assertJsonPath('data.0.unread_messages', 0);

        // Query count test on GET messages
        Sanctum::actingAs($this->customer, ['customer']);
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson("/api/v1/customer/orders/{$this->order->id}/messages")->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be bounded (token lookup + order lookup + messages select + unread count = <= 5 queries)
        $this->assertLessThanOrEqual(6, count($queries));
    }

    public function test_no_response_contains_sensitive_data(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $res = $this->postJson("/api/v1/customer/orders/{$this->order->id}/messages", [
            'message' => 'Clean message test',
        ]);

        $res->assertStatus(200);
        $json = $res->getContent();

        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString('remember_token', $json);
        $this->assertStringNotContainsString('fatima@example.com', $json);
        $this->assertStringNotContainsString('seller1@example.com', $json);
        $this->assertStringNotContainsString('03009998877', $json);
        $this->assertStringNotContainsString('storage/app', $json);
    }
}
