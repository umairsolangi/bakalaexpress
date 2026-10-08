<?php

namespace Tests\Feature\Api\Rider;

use App\Mail\OrderDeliveredMail;
use App\Models\CatalogCategory;
use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use App\Services\Api\RiderOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RiderOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Rider $rider;
    protected Rider $rider2;
    protected User $customer;
    protected Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->rider = Rider::create([
            'name' => 'Rashid Rider',
            'email' => 'rashid@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03111234567',
            'cnic_number' => '42101-1234567-1',
            'vehicle_type' => 'Motorcycle',
            'vehicle_number' => 'KHI-9988',
            'address' => 'Gulshan-e-Iqbal, Karachi',
            'status' => 'online',
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $this->rider2 = Rider::create([
            'name' => 'Tariq Rider',
            'email' => 'tariq@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03221234567',
            'cnic_number' => '42101-7654321-2',
            'vehicle_type' => 'Motorcycle',
            'vehicle_number' => 'KHI-7766',
            'address' => 'North Nazimabad, Karachi',
            'status' => 'online',
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $this->customer = User::create([
            'name' => 'Zainab Customer',
            'email' => 'zainab@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03001234567',
            'sellerType' => 0,
            'is_verified' => true,
        ]);

        $category = CatalogCategory::create([
            'name' => 'Supermarket',
            'slug' => 'supermarket',
            'is_active' => true,
        ]);

        $this->seller = Seller::create([
            'name' => 'Metro Grocery Store',
            'email' => 'metro@example.com',
            'password' => Hash::make('password123'),
            'phone_number' => '03331234567',
            'shop_name' => 'Metro Grocery Store',
            'profile_image' => 'profile_images/store.jpg',
            'city' => 'Karachi',
            'area' => 'Gulshan',
            'sector' => 'Block 5',
            'near_areas' => ['Disco Bakery'],
            'full_address' => 'Plot 44, Block 5, Gulshan, Karachi',
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
            'is_open' => true,
            'catalog_category_id' => $category->id,
            'accountIsApproved' => 1,
            'is_deleted' => false,
        ]);
    }

    protected function createOrder(array $attributes = []): Order
    {
        $fillableKeys = ['user_id', 'seller_id', 'address', 'phone', 'status', 'total_amount', 'transaction_id'];
        $fillable = [
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'Flat 402, Al-Razi Heights, Sector F-8, Islamabad',
            'phone' => '03001234567',
            'status' => 'ready_for_pickup',
            'total_amount' => 500.00,
        ];

        $nonFillable = [
            'delivery_charges' => 0.00,
            'discount_amount' => 0.00,
            'delivery_instructions' => 'Please leave at the door.',
        ];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $fillableKeys, true)) {
                $fillable[$key] = $value;
            } else {
                $nonFillable[$key] = $value;
            }
        }

        $order = Order::create($fillable);

        if (!empty($nonFillable)) {
            DB::table('orders')->where('id', $order->id)->update($nonFillable);
            $order->refresh();
        }

        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => 'Fresh Bread 400g',
            'unit_type' => 'piece',
            'quantity' => 2,
            'price' => 250.00,
        ]);

        return $order;
    }

    /**
     * Test: Unauthenticated request gets 401.
     */
    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/rider/dashboard');
        $response->assertStatus(401);
    }

    /**
     * Test: Customer, Seller, and Admin tokens get 403 on rider routes.
     */
    public function test_non_rider_tokens_are_forbidden(): void
    {
        // Customer token with 'customer' ability
        Sanctum::actingAs($this->customer, ['customer']);
        $this->getJson('/api/v1/rider/dashboard')->assertStatus(403);

        // Seller token with 'seller' ability
        Sanctum::actingAs($this->seller, ['seller']);
        $this->getJson('/api/v1/rider/dashboard')->assertStatus(403);

        // Admin token with 'admin' ability
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 1,
            'is_verified' => true,
        ]);
        Sanctum::actingAs($admin, ['admin']);
        $this->getJson('/api/v1/rider/dashboard')->assertStatus(403);
    }

    /**
     * Test: Rider rejected/unapproved after login loses access immediately.
     */
    public function test_unapproved_or_suspended_rider_is_immediately_forbidden(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        // First, check active rider has access
        $this->getJson('/api/v1/rider/dashboard')->assertStatus(200);

        // Disapprove rider in DB
        DB::table('riders')->where('id', $this->rider->id)->update(['is_approved' => 0]);

        $response = $this->getJson('/api/v1/rider/dashboard');
        $response->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN_ROLE');
    }

    /**
     * Test: Rider status toggle (online/offline) and prevention of 'busy' or offline with active orders.
     */
    public function test_rider_status_transitions(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        // Go offline
        $response = $this->postJson('/api/v1/rider/status', ['status' => 'offline']);
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'offline');

        $this->assertEquals('offline', $this->rider->fresh()->status);

        // Go online
        $response = $this->postJson('/api/v1/rider/status', ['status' => 'online']);
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'online');

        // Cannot set status to 'busy'
        $response = $this->postJson('/api/v1/rider/status', ['status' => 'busy']);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Cannot go offline if holding an active order
        $order = $this->createOrder();
        DB::table('orders')->where('id', $order->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'assigned_to_rider',
        ]);

        $response = $this->postJson('/api/v1/rider/status', ['status' => 'offline']);
        $response->assertStatus(422)
            ->assertJsonPath('code', 'HAS_ACTIVE_ORDERS');
    }

    /**
     * Test: Available orders endpoint shows only ready_for_pickup without rider, rejects offline riders, hides phone.
     */
    public function test_available_orders_endpoint(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        $readyOrder = $this->createOrder();
        $assignedOrder = $this->createOrder();
        DB::table('orders')->where('id', $assignedOrder->id)->update([
            'rider_id' => $this->rider2->id,
            'status' => 'assigned_to_rider',
        ]);

        $response = $this->getJson('/api/v1/rider/orders/available');
        $response->assertStatus(200)
            ->assertJsonPath('meta.suggested_poll_seconds', 15)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $readyOrder->id);

        // Sensitive customer details must NOT be exposed before acceptance
        $response->assertJsonMissing(['phone' => '03001234567']);
        $response->assertJsonMissing(['name' => 'Zainab Customer']);

        // When offline, available orders endpoint rejects with 422 RIDER_OFFLINE
        $this->rider->update(['status' => 'offline']);
        $this->getJson('/api/v1/rider/orders/available')
            ->assertStatus(422)
            ->assertJsonPath('code', 'RIDER_OFFLINE');
    }

    /**
     * Test: Order acceptance - success path, notification sent, fillable check.
     */
    public function test_rider_can_accept_available_order(): void
    {
        Notification::fake();
        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder();

        $response = $this->postJson("/api/v1/rider/orders/{$order->id}/accept");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.status', 'assigned_to_rider')
            ->assertJsonPath('data.customer.name', 'Zainab Customer')
            ->assertJsonPath('data.customer.phone', '03001234567');

        $this->assertEquals($this->rider->id, $order->fresh()->rider_id);
        $this->assertEquals('assigned_to_rider', $order->fresh()->status);

        Notification::assertSentTo(
            $this->customer,
            OrderStatusNotification::class,
            fn ($n, $channels, $notifiable) => $n->toDatabase($notifiable)['status'] === 'assigned_to_rider'
        );

        // Verify Order::$fillable does NOT contain rider_id (Hard Rule 3)
        $this->assertNotContains('rider_id', (new Order())->getFillable());
    }

    /**
     * Test: Race condition - two riders accept the same order; only one wins, other gets 409 ORDER_ALREADY_TAKEN.
     */
    public function test_concurrent_acceptance_race_condition(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);
        $order = $this->createOrder();

        // Rider 1 accepts successfully
        $res1 = $this->postJson("/api/v1/rider/orders/{$order->id}/accept");
        $res1->assertStatus(200);

        // Rider 2 tries to accept the same order
        Sanctum::actingAs($this->rider2, ['rider']);
        $res2 = $this->postJson("/api/v1/rider/orders/{$order->id}/accept");
        $res2->assertStatus(409)
            ->assertJsonPath('code', 'ORDER_ALREADY_TAKEN');
    }

    /**
     * Test: Cancelled order cannot be accepted (returns 422 ORDER_NOT_AVAILABLE).
     */
    public function test_cannot_accept_cancelled_order(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);
        $order = $this->createOrder(['status' => 'cancelled']);

        $response = $this->postJson("/api/v1/rider/orders/{$order->id}/accept");
        $response->assertStatus(422)
            ->assertJsonPath('code', 'ORDER_NOT_AVAILABLE');
    }

    /**
     * Test: Active orders limit (rider_max_active_orders = 2).
     */
    public function test_active_orders_limit_enforced(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);
        Config::set('bakala_orders.rider_max_active_orders', 2);

        $order1 = $this->createOrder();
        $order2 = $this->createOrder();
        $order3 = $this->createOrder();

        $this->postJson("/api/v1/rider/orders/{$order1->id}/accept")->assertStatus(200);
        $this->postJson("/api/v1/rider/orders/{$order2->id}/accept")->assertStatus(200);

        // Accepting 3rd order exceeds limit of 2
        $response = $this->postJson("/api/v1/rider/orders/{$order3->id}/accept");
        $response->assertStatus(422)
            ->assertJsonPath('code', 'TOO_MANY_ACTIVE_ORDERS');
    }

    /**
     * Test: Pickup transitions from assigned_to_rider to picked_up with notification.
     */
    public function test_rider_can_pickup_order(): void
    {
        Notification::fake();
        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder();
        DB::table('orders')->where('id', $order->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'assigned_to_rider',
        ]);

        $response = $this->postJson("/api/v1/rider/orders/{$order->id}/pickup");
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'picked_up')
            ->assertJsonPath('data.can_pickup', false)
            ->assertJsonPath('data.can_deliver', true);

        $this->assertEquals('picked_up', $order->fresh()->status);

        Notification::assertSentTo(
            $this->customer,
            OrderStatusNotification::class,
            fn ($n, $channels, $notifiable) => $n->toDatabase($notifiable)['status'] === 'picked_up'
        );

        // Calling pickup again returns 422 INVALID_STATUS_TRANSITION
        $this->postJson("/api/v1/rider/orders/{$order->id}/pickup")
            ->assertStatus(422)
            ->assertJsonPath('code', 'INVALID_STATUS_TRANSITION');
    }

    /**
     * Test: Another rider accessing or mutating the order gets 404 NOT_FOUND.
     */
    public function test_another_riders_order_returns_404(): void
    {
        $order = $this->createOrder();
        DB::table('orders')->where('id', $order->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'assigned_to_rider',
        ]);

        Sanctum::actingAs($this->rider2, ['rider']);

        $this->getJson("/api/v1/rider/orders/{$order->id}")->assertStatus(404);
        $this->postJson("/api/v1/rider/orders/{$order->id}/pickup")->assertStatus(404);
        $this->postJson("/api/v1/rider/orders/{$order->id}/deliver")->assertStatus(404);
    }

    /**
     * Test: Deliver order - success without proof when require_delivery_proof = false.
     */
    public function test_deliver_order_without_proof_when_optional(): void
    {
        Notification::fake();
        Mail::fake();
        Config::set('bakala_orders.require_delivery_proof', false);

        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder();
        DB::table('orders')->where('id', $order->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'picked_up',
        ]);

        $response = $this->postJson("/api/v1/rider/orders/{$order->id}/deliver");
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('data.can_deliver', false);

        $this->assertEquals('delivered', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->estimated_delivery_at);
        $this->assertEquals('online', $this->rider->fresh()->status);

        Notification::assertSentTo(
            $this->customer,
            OrderStatusNotification::class,
            fn ($n, $channels, $notifiable) => $n->toDatabase($notifiable)['status'] === 'delivered'
        );

        Mail::assertSent(OrderDeliveredMail::class, function ($mail) use ($order) {
            return $mail->hasTo($this->customer->email) && $mail->order->id === $order->id;
        });

        // Double deliver returns 422 and does NOT send second email
        Mail::fake();
        $this->postJson("/api/v1/rider/orders/{$order->id}/deliver")
            ->assertStatus(422)
            ->assertJsonPath('code', 'INVALID_STATUS_TRANSITION');

        Mail::assertNothingSent();
    }

    /**
     * Test: Deliver order requires proof when require_delivery_proof = true.
     */
    public function test_deliver_order_requires_proof_when_configured(): void
    {
        Config::set('bakala_orders.require_delivery_proof', true);
        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder();
        DB::table('orders')->where('id', $order->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'picked_up',
        ]);

        $response = $this->postJson("/api/v1/rider/orders/{$order->id}/deliver");
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['proof_image']);
    }

    /**
     * Test: Upload validation rejects oversized or non-image files.
     */
    public function test_deliver_proof_upload_validation(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder();
        DB::table('orders')->where('id', $order->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'picked_up',
        ]);

        // Non-image upload
        $textDoc = UploadedFile::fake()->create('malicious.txt', 100, 'text/plain');
        $this->postJson("/api/v1/rider/orders/{$order->id}/deliver", [
            'proof_image' => $textDoc,
        ])->assertStatus(422)->assertJsonValidationErrors(['proof_image']);

        // Oversized file (> 4096 KB)
        $hugeFile = UploadedFile::fake()->create('huge.jpg', 5000, 'image/jpeg');
        $this->postJson("/api/v1/rider/orders/{$order->id}/deliver", [
            'proof_image' => $hugeFile,
        ])->assertStatus(422)->assertJsonValidationErrors(['proof_image']);
    }

    /**
     * Test A3: Real GD sanitizer delivers with JPEG and PNG, re-encodes (no Exif), saves UUID .jpg,
     * returns absolute URLs across endpoints, and rejects fake images without saving.
     */
    public function test_real_sanitizer_proof_upload_and_security(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        // 1. Deliver with generated JPEG
        $orderJpeg = $this->createOrder();
        DB::table('orders')->where('id', $orderJpeg->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'picked_up',
        ]);

        $jpegFile = UploadedFile::fake()->image('proof.jpg', 600, 400);
        $resJpeg = $this->postJson("/api/v1/rider/orders/{$orderJpeg->id}/deliver", [
            'proof_image' => $jpegFile,
        ]);
        $resJpeg->assertStatus(200);

        $storedPathJpeg = $orderJpeg->fresh()->delivery_proof_image;
        $this->assertNotNull($storedPathJpeg);
        $this->assertStringStartsWith('delivery_proofs/', $storedPathJpeg);
        $this->assertStringEndsWith('.jpg', $storedPathJpeg);
        Storage::disk('public')->assertExists($storedPathJpeg);

        // Assert re-encoded: Exif APP1 marker (\xFF\xE1) must NOT be present
        $storedBytes = Storage::disk('public')->get($storedPathJpeg);
        $this->assertStringStartsWith("\xFF\xD8\xFF", $storedBytes); // Valid JPEG SOI
        $this->assertStringNotContainsString("\xFF\xE1", substr($storedBytes, 0, 100)); // No Exif marker

        // Assert absolute URLs across Rider and Customer endpoints
        $expectedUrlJpeg = asset('storage/' . $storedPathJpeg);
        $this->assertEquals($expectedUrlJpeg, $resJpeg->json('data.delivery_proof_image'));

        $riderShowRes = $this->getJson("/api/v1/rider/orders/{$orderJpeg->id}");
        $riderShowRes->assertStatus(200);
        $this->assertEquals($expectedUrlJpeg, $riderShowRes->json('data.delivery_proof_image'));

        Sanctum::actingAs($this->customer, ['customer']);
        $custShowRes = $this->getJson("/api/v1/customer/orders/{$orderJpeg->id}");
        $custShowRes->assertStatus(200);
        $this->assertEquals($expectedUrlJpeg, $custShowRes->json('data.delivery_proof_image'));

        // 2. Deliver with generated PNG (sanitized to safe .jpg)
        Sanctum::actingAs($this->rider, ['rider']);
        $orderPng = $this->createOrder();
        DB::table('orders')->where('id', $orderPng->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'picked_up',
        ]);

        $pngFile = UploadedFile::fake()->image('proof.png', 500, 500);
        $resPng = $this->postJson("/api/v1/rider/orders/{$orderPng->id}/deliver", [
            'proof_image' => $pngFile,
        ]);
        $resPng->assertStatus(200);

        $storedPathPng = $orderPng->fresh()->delivery_proof_image;
        $this->assertNotNull($storedPathPng);
        $this->assertStringEndsWith('.jpg', $storedPathPng); // Re-encoded to .jpg canvas
        Storage::disk('public')->assertExists($storedPathPng);

        // 3. Fake image (text file renamed to .jpg) is rejected and saves nothing
        $orderFake = $this->createOrder();
        DB::table('orders')->where('id', $orderFake->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'picked_up',
        ]);

        $fakeJpg = UploadedFile::fake()->createWithContent('malicious.jpg', 'THIS IS NOT AN IMAGE');
        $resFake = $this->postJson("/api/v1/rider/orders/{$orderFake->id}/deliver", [
            'proof_image' => $fakeJpg,
        ]);
        $resFake->assertStatus(422);
        $this->assertNull($orderFake->fresh()->delivery_proof_image);
    }

    /**
     * Test A2: Rider privacy - customer_phone and customer_address removed from history,
     * first name only and area hint shown; delivered order show hides phone and replaces address with area hint.
     */
    public function test_rider_privacy_in_history_and_terminal_orders(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        // Create a delivered order
        $deliveredOrder = $this->createOrder([
            'rider_id' => $this->rider->id,
            'status' => 'delivered',
        ]);

        // Create an active order
        $activeOrder = $this->createOrder([
            'rider_id' => $this->rider->id,
            'status' => 'assigned_to_rider',
        ]);

        // 1. History endpoint
        $histRes = $this->getJson('/api/v1/rider/history');
        $histRes->assertStatus(200);

        $firstHistoryItem = $histRes->json('data.0');
        $this->assertArrayNotHasKey('customer_phone', $firstHistoryItem);
        $this->assertArrayNotHasKey('customer_address', $firstHistoryItem);
        $this->assertEquals('Zainab', $firstHistoryItem['customer_name']); // First name only
        $this->assertArrayHasKey('customer_area_hint', $firstHistoryItem);

        // 2. Order detail endpoint on DELIVERED order
        $deliveredShowRes = $this->getJson("/api/v1/rider/orders/{$deliveredOrder->id}");
        $deliveredShowRes->assertStatus(200)
            ->assertJsonPath('data.customer.name', 'Zainab Customer')
            ->assertJsonPath('data.customer.phone', null); // Phone hidden

        // 3. Order detail endpoint on ACTIVE order
        $activeShowRes = $this->getJson("/api/v1/rider/orders/{$activeOrder->id}");
        $activeShowRes->assertStatus(200)
            ->assertJsonPath('data.customer.phone', '03001234567') // Real phone visible
            ->assertJsonPath('data.customer.address', 'Flat 402, Al-Razi Heights, Sector F-8, Islamabad'); // Full address visible
    }

    /**
     * Test A1: Raw response body string has "meta":{} and never "meta":[] across auth, customer, and rider.
     */
    public function test_raw_json_serializes_empty_meta_as_object_across_roles(): void
    {
        // 1. Auth endpoint (POST /customer/auth/login)
        $authRes = $this->postJson('/api/v1/customer/auth/login', [
            'email' => $this->customer->email,
            'password' => 'password123',
        ]);
        $authRes->assertStatus(200);
        $this->assertStringContainsString('"meta":{}', $authRes->getContent());
        $this->assertStringNotContainsString('"meta":[]', $authRes->getContent());

        // 2. Rider endpoint (POST /rider/status)
        Sanctum::actingAs($this->rider, ['rider']);
        $riderRes = $this->postJson('/api/v1/rider/status', ['status' => 'online']);
        $riderRes->assertStatus(200);
        $this->assertStringContainsString('"meta":{}', $riderRes->getContent());
        $this->assertStringNotContainsString('"meta":[]', $riderRes->getContent());

        // 3. Customer endpoint (GET /customer/auth/me)
        Sanctum::actingAs($this->customer, ['customer']);
        $custRes = $this->getJson('/api/v1/customer/auth/me');
        $custRes->assertStatus(200);
        $this->assertStringContainsString('"meta":{}', $custRes->getContent());
        $this->assertStringNotContainsString('"meta":[]', $custRes->getContent());
    }

    /**
     * Test: Delivery proof URL is formatted identically to Stage 4 customer order resource.
     */
    public function test_delivery_proof_url_matches_customer_detail_resource(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder([
            'rider_id' => $this->rider->id,
            'status' => 'delivered',
            'delivery_proof_image' => 'delivery_proofs/sample-proof.jpg',
        ]);

        $riderRes = $this->getJson("/api/v1/rider/orders/{$order->id}");
        $riderRes->assertStatus(200);
        $expectedUrl = asset('storage/delivery_proofs/sample-proof.jpg');
        $this->assertEquals($expectedUrl, $riderRes->json('data.delivery_proof_image'));

        // Customer order show also exposes the same URL
        Sanctum::actingAs($this->customer, ['customer']);
        $custRes = $this->getJson("/api/v1/customer/orders/{$order->id}");
        $custRes->assertStatus(200);
        $this->assertEquals($expectedUrl, $custRes->json('data.delivery_proof_image'));
    }

    /**
     * Test: Email failure does not fail or rollback order delivery (Hard Rule 8).
     */
    public function test_email_failure_does_not_rollback_delivery(): void
    {
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('SMTP Connection Timed Out'));

        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder();
        DB::table('orders')->where('id', $order->id)->update([
            'rider_id' => $this->rider->id,
            'status' => 'picked_up',
        ]);

        $response = $this->postJson("/api/v1/rider/orders/{$order->id}/deliver");
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');

        $this->assertEquals('delivered', $order->fresh()->status);
    }

    /**
     * Test: Dashboard and History counts and earnings match hand calculation, earnings_note is present.
     */
    public function test_dashboard_and_history_metrics(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        // Create 2 delivered orders today with 30.00 and 40.00 delivery charges
        $order1 = $this->createOrder([
            'rider_id' => $this->rider->id,
            'status' => 'delivered',
            'delivery_charges' => 30.00,
        ]);
        $order2 = $this->createOrder([
            'rider_id' => $this->rider->id,
            'status' => 'completed',
            'delivery_charges' => 40.00,
        ]);

        // Create 1 active order
        $order3 = $this->createOrder([
            'rider_id' => $this->rider->id,
            'status' => 'assigned_to_rider',
            'delivery_charges' => 25.00,
        ]);

        // Dashboard check
        $dashRes = $this->getJson('/api/v1/rider/dashboard');
        $dashRes->assertStatus(200)
            ->assertJsonPath('data.today_delivered_count', 1) // status = 'delivered'
            ->assertJsonPath('data.today_earnings', '70.00') // sum of delivered + completed = 70.00
            ->assertJsonPath('data.active_orders.0.id', $order3->id)
            ->assertJsonStructure(['data' => ['earnings_note'], 'meta' => ['server_time']]);

        // History check
        $histRes = $this->getJson('/api/v1/rider/history');
        $histRes->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total_deliveries', 2)
            ->assertJsonPath('meta.total_earnings', '70.00')
            ->assertJsonPath('meta.today_deliveries', 2)
            ->assertJsonPath('meta.today_earnings', '70.00')
            ->assertJsonStructure(['meta' => ['earnings_note']]);
    }

    /**
     * Test: Responses never leak sensitive fields (cnic, password, remember_token, otp, storage paths, emails).
     */
    public function test_responses_never_leak_sensitive_data(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        $order = $this->createOrder([
            'rider_id' => $this->rider->id,
            'status' => 'assigned_to_rider',
        ]);

        $endpoints = [
            '/api/v1/rider/dashboard',
            '/api/v1/rider/orders/available',
            '/api/v1/rider/orders/current',
            "/api/v1/rider/orders/{$order->id}",
            '/api/v1/rider/history',
        ];

        foreach ($endpoints as $url) {
            $response = $this->getJson($url);
            $content = $response->getContent();

            $this->assertStringNotContainsString('remember_token', $content);
            $this->assertStringNotContainsString('password', $content);
            $this->assertStringNotContainsString('cnic_number', $content);
            $this->assertStringNotContainsString('42101-1234567-1', $content);
            $this->assertStringNotContainsString('zainab@example.com', $content);
            $this->assertStringNotContainsString('metro@example.com', $content);
        }
    }

    /**
     * Test: Query count performance for GET /orders/available and GET /history.
     */
    public function test_query_count_for_available_and_history_lists(): void
    {
        Sanctum::actingAs($this->rider, ['rider']);

        // Create multiple available and delivered orders
        for ($i = 0; $i < 5; $i++) {
            $this->createOrder();
            $this->createOrder([
                'rider_id' => $this->rider->id,
                'status' => 'delivered',
            ]);
        }

        // Available orders query count test
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/v1/rider/orders/available')->assertStatus(200);
        $availableQueries = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(10, $availableQueries, "Available orders should run <= 10 queries");

        // History query count test
        DB::flushQueryLog();
        $this->getJson('/api/v1/rider/history')->assertStatus(200);
        $historyQueries = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(10, $historyQueries, "History should run <= 10 queries");
    }
}
