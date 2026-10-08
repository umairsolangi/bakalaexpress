<?php

namespace Tests\Feature\Api;

use App\Events\OrderReadyForPickup;
use App\Models\CatalogCategory;
use App\Models\DeviceToken;
use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use App\Services\Api\PushService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $customer2;
    protected Seller $seller;
    protected Rider $rider1;
    protected Rider $rider2;
    protected CatalogCategory $category;
    protected GlobalProduct $globalProduct;
    protected ShopProduct $shopProduct;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Mail::fake();

        Config::set('push.enabled', true);

        $this->category = CatalogCategory::create([
            'name' => 'General Store',
            'slug' => 'general-store',
            'is_active' => true,
        ]);

        $this->seller = Seller::create([
            'name' => 'Madina Store',
            'email' => 'madina@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'area' => 'Gulshan',
            'sector' => 'Block 5',
            'full_address' => 'Shop 10, Block 5, Gulshan',
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
            'is_open' => true,
            'catalog_category_id' => $this->category->id,
            'accountIsApproved' => 1,
            'is_deleted' => 0,
        ]);

        $this->customer = User::create([
            'name' => 'Aisha Customer',
            'email' => 'aisha@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03001234567',
            'city' => 'Karachi',
            'sellerType' => 0,
            'is_verified' => true,
        ]);

        $this->customer2 = User::create([
            'name' => 'Bilal Customer',
            'email' => 'bilal@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03007654321',
            'city' => 'Karachi',
            'sellerType' => 0,
            'is_verified' => true,
        ]);

        $this->rider1 = Rider::create([
            'name' => 'Tariq Rider',
            'email' => 'tariq@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03121111111',
            'cnic_number' => '42101-1111111-1',
            'vehicle_type' => 'Motorcycle',
            'vehicle_number' => 'KHI-1111',
            'address' => 'Gulshan, Karachi',
            'status' => 'online',
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $this->rider2 = Rider::create([
            'name' => 'Zubair Rider',
            'email' => 'zubair@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03122222222',
            'cnic_number' => '42101-2222222-2',
            'vehicle_type' => 'Motorcycle',
            'vehicle_number' => 'KHI-2222',
            'address' => 'Clifton, Karachi',
            'status' => 'offline', // Offline
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $this->globalProduct = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'Olpers Milk 1L',
            'slug' => 'olpers-milk-1l',
            'base_price' => 280.00,
            'unit_type' => 'piece',
            'is_active' => true,
        ]);

        $this->shopProduct = ShopProduct::create([
            'seller_id' => $this->seller->id,
            'global_product_id' => $this->globalProduct->id,
            'custom_price' => 280.00,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);
    }

    protected function createOrder(array $attributes = []): Order
    {
        $order = Order::create(array_merge([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'Flat 201, Gulshan Heights, Karachi',
            'phone' => '03001234567',
            'status' => 'pending',
            'total_amount' => 560.00,
        ], $attributes));

        OrderItem::create([
            'order_id' => $order->id,
            'shop_product_id' => $this->shopProduct->id,
            'global_product_id' => $this->globalProduct->id,
            'item_name' => 'Olpers Milk 1L',
            'unit_type' => 'piece',
            'quantity' => 2,
            'price' => 280.00,
        ]);

        return $order->fresh(['seller', 'user', 'items']);
    }

    // -------------------------------------------------------------
    // DEVICE TOKEN MANAGEMENT TESTS
    // -------------------------------------------------------------

    public function test_register_device_token_valid(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $res = $this->postJson('/api/v1/customer/devices', [
            'expo_push_token' => 'ExponentPushToken[AbCdEf123456_-xyz]',
            'platform' => 'android',
            'device_name' => 'Samsung S22',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token', 'ExponentPushToken[AbCdEf123456_-xyz]')
            ->assertJsonPath('data.platform', 'android');

        $this->assertDatabaseHas('device_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[AbCdEf123456_-xyz]',
            'platform' => 'android',
        ]);
    }

    public function test_register_device_token_invalid_format_is_rejected(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $res = $this->postJson('/api/v1/customer/devices', [
            'expo_push_token' => 'invalid-fcm-token-12345',
            'platform' => 'android',
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['expo_push_token']);
    }

    public function test_register_device_token_moves_between_accounts_on_shared_phone(): void
    {
        // 1. Customer 1 registers token
        Sanctum::actingAs($this->customer, ['customer']);
        $this->postJson('/api/v1/customer/devices', [
            'expo_push_token' => 'ExponentPushToken[SharedPhoneToken123]',
            'platform' => 'ios',
        ])->assertStatus(200);

        $this->assertDatabaseHas('device_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[SharedPhoneToken123]',
        ]);

        // 2. Customer 2 registers the SAME token
        Sanctum::actingAs($this->customer2, ['customer']);
        $this->postJson('/api/v1/customer/devices', [
            'expo_push_token' => 'ExponentPushToken[SharedPhoneToken123]',
            'platform' => 'ios',
        ])->assertStatus(200);

        // Token must now belong to Customer 2, not Customer 1
        $this->assertDatabaseHas('device_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer2->id,
            'token' => 'ExponentPushToken[SharedPhoneToken123]',
        ]);

        $this->assertDatabaseMissing('device_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[SharedPhoneToken123]',
        ]);
    }

    public function test_register_device_token_enforces_max_5_per_account(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        for ($i = 1; $i <= 6; $i++) {
            $this->postJson('/api/v1/customer/devices', [
                'expo_push_token' => "ExponentPushToken[Token_{$i}]",
                'platform' => 'android',
            ])->assertStatus(200);
        }

        // Account must have exactly 5 tokens, and oldest Token_1 should have been deleted
        $tokens = DeviceToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $this->customer->id)
            ->pluck('token')
            ->all();

        $this->assertCount(5, $tokens);
        $this->assertNotContains('ExponentPushToken[Token_1]', $tokens);
        $this->assertContains('ExponentPushToken[Token_6]', $tokens);
    }

    public function test_delete_device_token(): void
    {
        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[DeleteMeToken]',
            'platform' => 'android',
        ]);

        Sanctum::actingAs($this->customer, ['customer']);

        $res = $this->deleteJson('/api/v1/customer/devices', [
            'expo_push_token' => 'ExponentPushToken[DeleteMeToken]',
        ]);

        $res->assertStatus(200)->assertJsonPath('success', true);
        $this->assertDatabaseMissing('device_tokens', ['token' => 'ExponentPushToken[DeleteMeToken]']);
    }

    public function test_delete_device_token_cannot_delete_other_user_token(): void
    {
        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer2->id,
            'token' => 'ExponentPushToken[OtherUserToken]',
            'platform' => 'android',
        ]);

        Sanctum::actingAs($this->customer, ['customer']);

        $res = $this->deleteJson('/api/v1/customer/devices', [
            'expo_push_token' => 'ExponentPushToken[OtherUserToken]',
        ]);

        $res->assertStatus(200);
        // Token still exists for customer 2
        $this->assertDatabaseHas('device_tokens', ['token' => 'ExponentPushToken[OtherUserToken]']);
    }

    public function test_device_endpoints_auth_and_abilities(): void
    {
        // Unauthenticated 401
        $this->postJson('/api/v1/customer/devices', [])->assertStatus(401);
        $this->postJson('/api/v1/seller/devices', [])->assertStatus(401);
        $this->postJson('/api/v1/rider/devices', [])->assertStatus(401);

        // Wrong ability 403
        Sanctum::actingAs($this->customer, ['customer']);
        $this->postJson('/api/v1/seller/devices', [])->assertStatus(403);
        $this->postJson('/api/v1/rider/devices', [])->assertStatus(403);
    }

    // -------------------------------------------------------------
    // NOTIFICATION EVENT LISTENER PUSH TESTS
    // -------------------------------------------------------------

    public function test_new_order_notification_triggers_seller_push(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['data' => [['status' => 'ok']]], 200),
        ]);

        DeviceToken::create([
            'tokenable_type' => Seller::class,
            'tokenable_id' => $this->seller->id,
            'token' => 'ExponentPushToken[SellerOrderToken]',
            'platform' => 'android',
        ]);

        $order = $this->createOrder();

        // Dispatch notification the way the web and API do
        $this->seller->notify(new NewOrderNotification($this->seller->name, $order->id, json_encode([['service_id' => 1, 'quantity' => 1, 'price' => 280]])));

        Http::assertSent(function (HttpClientRequest $request) use ($order) {
            $data = $request->data();
            $msg = $data[0] ?? [];
            return $msg['to'] === 'ExponentPushToken[SellerOrderToken]'
                && str_contains($msg['title'], "New order #{$order->id}")
                && $msg['data']['type'] === 'new_order'
                && $msg['data']['role'] === 'seller';
        });
    }

    public function test_order_status_notification_triggers_customer_push_for_all_statuses(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['data' => [['status' => 'ok']]], 200),
        ]);

        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[CustomerStatusToken]',
            'platform' => 'ios',
        ]);

        $order = $this->createOrder();
        $statuses = [
            'confirmed_by_seller' => 'Order confirmed',
            'preparing' => 'Being prepared',
            'ready_for_pickup' => 'Ready for pickup',
            'assigned_to_rider' => 'Rider assigned',
            'picked_up' => 'On the way',
            'delivered' => 'Delivered',
            'cancelled' => 'Order cancelled',
            'rejected' => 'Order rejected',
        ];

        foreach ($statuses as $status => $expectedBodyText) {
            $this->customer->notify(new OrderStatusNotification($order, $status, 'Rashid'));

            Http::assertSent(function (HttpClientRequest $request) use ($expectedBodyText, $status) {
                $data = $request->data();
                $msg = $data[0] ?? [];
                return $msg['to'] === 'ExponentPushToken[CustomerStatusToken]'
                    && str_contains($msg['body'], $expectedBodyText)
                    && $msg['data']['type'] === 'order_status'
                    && $msg['data']['status'] === $status;
            });
        }
    }

    public function test_order_ready_for_pickup_pushes_only_to_online_approved_riders(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['data' => [['status' => 'ok']]], 200),
        ]);

        // Online approved rider 1
        DeviceToken::create([
            'tokenable_type' => Rider::class,
            'tokenable_id' => $this->rider1->id,
            'token' => 'ExponentPushToken[Rider1Token]',
            'platform' => 'android',
        ]);

        // Offline rider 2
        DeviceToken::create([
            'tokenable_type' => Rider::class,
            'tokenable_id' => $this->rider2->id,
            'token' => 'ExponentPushToken[Rider2Token]',
            'platform' => 'android',
        ]);

        $order = $this->createOrder();

        // Fire OrderReadyForPickup broadcast event
        event(new OrderReadyForPickup($order));

        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();
            $tokens = array_column($data, 'to');
            return in_array('ExponentPushToken[Rider1Token]', $tokens, true)
                && !in_array('ExponentPushToken[Rider2Token]', $tokens, true);
        });
    }

    // -------------------------------------------------------------
    // REAL API LIFECYCLE PUSH DISPATCH TESTS
    // -------------------------------------------------------------

    public function test_real_api_flows_trigger_push_notifications(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['data' => [['status' => 'ok']]], 200),
        ]);

        // Assign tokens
        DeviceToken::create([
            'tokenable_type' => Seller::class,
            'tokenable_id' => $this->seller->id,
            'token' => 'ExponentPushToken[FlowSellerToken]',
            'platform' => 'android',
        ]);

        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[FlowCustomerToken]',
            'platform' => 'ios',
        ]);

        DeviceToken::create([
            'tokenable_type' => Rider::class,
            'tokenable_id' => $this->rider1->id,
            'token' => 'ExponentPushToken[FlowRiderToken]',
            'platform' => 'android',
        ]);

        // 1. Customer places order -> Seller push
        Sanctum::actingAs($this->customer, ['customer']);
        $orderRes = $this->withHeaders([
            'Idempotency-Key' => 'push-flow-order-key-12345678901234567',
        ])->postJson('/api/v1/customer/orders', [
            'seller_id' => $this->seller->id,
            'address' => 'Block 5, Gulshan',
            'phone' => '03001234567',
            'payment_method' => 'cod',
            'items' => [
                ['listing_id' => $this->shopProduct->id, 'quantity' => 1]
            ],
        ]);
        $orderRes->assertStatus(201);
        $orderId = $orderRes->json('data.id');

        Http::assertSent(function (HttpClientRequest $req) use ($orderId) {
            $msg = $req->data()[0] ?? [];
            return $msg['to'] === 'ExponentPushToken[FlowSellerToken]'
                && str_contains($msg['title'], "New order #{$orderId}");
        });

        // 2. Seller confirms order -> Customer push
        Sanctum::actingAs($this->seller, ['seller']);
        $this->postJson("/api/v1/seller/orders/{$orderId}/confirm")->assertStatus(200);

        Http::assertSent(function (HttpClientRequest $req) {
            $msg = $req->data()[0] ?? [];
            return $msg['to'] === 'ExponentPushToken[FlowCustomerToken]'
                && str_contains($msg['body'], 'Order confirmed');
        });

        // 3. Seller marks ready -> Rider broadcast push
        $this->postJson("/api/v1/seller/orders/{$orderId}/prepare")->assertStatus(200);
        $this->postJson("/api/v1/seller/orders/{$orderId}/ready")->assertStatus(200);

        Http::assertSent(function (HttpClientRequest $req) {
            $msg = $req->data()[0] ?? [];
            return $msg['to'] === 'ExponentPushToken[FlowRiderToken]'
                && $msg['title'] === 'New order ready';
        });

        // 4. Rider accepts and delivers -> Customer push
        Sanctum::actingAs($this->rider1, ['rider']);
        $this->postJson("/api/v1/rider/orders/{$orderId}/accept")->assertStatus(200);
        $this->postJson("/api/v1/rider/orders/{$orderId}/pickup")->assertStatus(200);
        $this->postJson("/api/v1/rider/orders/{$orderId}/deliver")->assertStatus(200);

        Http::assertSent(function (HttpClientRequest $req) {
            $msg = $req->data()[0] ?? [];
            return $msg['to'] === 'ExponentPushToken[FlowCustomerToken]'
                && str_contains($msg['body'], 'Delivered');
        });
    }

    // -------------------------------------------------------------
    // EXPO ERROR HANDLING & SAFETY TESTS
    // -------------------------------------------------------------

    public function test_device_not_registered_deletes_token_cleanly(): void
    {
        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[DeadToken]',
            'platform' => 'android',
        ]);

        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    [
                        'status' => 'error',
                        'message' => '"ExponentPushToken[DeadToken]" is not registered',
                        'details' => ['error' => 'DeviceNotRegistered'],
                    ],
                ],
            ], 200),
        ]);

        $service = app(PushService::class);
        $service->sendToAccount($this->customer, 'Test', 'Body', []);

        $this->assertDatabaseMissing('device_tokens', ['token' => 'ExponentPushToken[DeadToken]']);
    }

    public function test_expo_500_or_timeout_does_not_fail_business_operations(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['error' => 'Internal server error'], 500),
        ]);

        DeviceToken::create([
            'tokenable_type' => Seller::class,
            'tokenable_id' => $this->seller->id,
            'token' => 'ExponentPushToken[SellerTokenErrorTest]',
            'platform' => 'android',
        ]);

        // Placing order still succeeds even when push endpoint fails
        Sanctum::actingAs($this->customer, ['customer']);
        $res = $this->withHeaders([
            'Idempotency-Key' => 'push-error-order-key-12345678901234567',
        ])->postJson('/api/v1/customer/orders', [
            'seller_id' => $this->seller->id,
            'address' => 'Gulshan Karachi',
            'phone' => '03001234567',
            'payment_method' => 'cod',
            'items' => [
                ['listing_id' => $this->shopProduct->id, 'quantity' => 1]
            ],
        ]);

        $res->assertStatus(201)->assertJsonPath('success', true);
    }

    public function test_push_disabled_sends_nothing(): void
    {
        Config::set('push.enabled', false);

        Http::fake();

        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[DisabledToken]',
            'platform' => 'android',
        ]);

        $service = app(PushService::class);
        $service->sendToAccount($this->customer, 'Title', 'Body', []);

        Http::assertNothingSent();
    }

    public function test_push_payloads_never_contain_phone_full_address_or_email(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['data' => [['status' => 'ok']]], 200),
        ]);

        DeviceToken::create([
            'tokenable_type' => Seller::class,
            'tokenable_id' => $this->seller->id,
            'token' => 'ExponentPushToken[PrivacyToken]',
            'platform' => 'android',
        ]);

        $order = $this->createOrder();
        $this->seller->notify(new NewOrderNotification($this->seller->name, $order->id, json_encode([['service_id' => 1, 'quantity' => 1, 'price' => 280]])));

        Http::assertSent(function (HttpClientRequest $req) {
            $bodyStr = json_encode($req->data());
            return !str_contains($bodyStr, '03001234567')
                && !str_contains($bodyStr, 'Flat 201, Gulshan Heights')
                && !str_contains($bodyStr, 'aisha@example.com');
        });
    }

    public function test_deduplication_suppresses_rapid_duplicate_pushes(): void
    {
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['data' => [['status' => 'ok']]], 200),
        ]);

        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[DedupeToken]',
            'platform' => 'android',
        ]);

        $service = app(PushService::class);
        $payload = ['type' => 'order_status', 'order_id' => '99', 'role' => 'customer'];

        // 1st call sends
        $service->sendToAccount($this->customer, 'Title 1', 'Body 1', $payload);
        Http::assertSentCount(1);

        // 2nd call within window suppressed
        $service->sendToAccount($this->customer, 'Title 2', 'Body 2', $payload);
        Http::assertSentCount(1);

        // Travel 15 seconds ahead (past 10s dedupe window)
        $this->travel(15)->seconds();

        // 3rd call sends again
        $service->sendToAccount($this->customer, 'Title 3', 'Body 3', $payload);
        Http::assertSentCount(2);
    }
}
