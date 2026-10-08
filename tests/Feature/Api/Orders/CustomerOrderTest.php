<?php

namespace Tests\Feature\Api\Orders;

use App\Mail\OrderCancelledMail;
use App\Mail\OrderPlacedMail;
use App\Models\CatalogCategory;
use App\Models\Feedback;
use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Seller $seller;
    protected CatalogCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->category = CatalogCategory::create([
            'name' => 'Groceries',
            'slug' => 'groceries',
            'is_active' => true,
        ]);

        $this->seller = Seller::create([
            'name' => 'Al-Madina Mart',
            'email' => 'almadina@example.com',
            'password' => Hash::make('password123'),
            'phone_number' => '03001234567',
            'shop_name' => 'Al-Madina Mart',
            'profile_image' => 'profile_images/store1.jpg',
            'city' => 'Karachi',
            'area' => 'Baldia Town',
            'sector' => '4A',
            'near_areas' => ['Ali Chowk'],
            'full_address' => 'Shop 1, Sector 4A, Karachi',
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
            'is_open' => true,
            'accountIsApproved' => 1,
            'is_deleted' => false,
            'catalog_category_id' => $this->category->id,
        ]);

        $this->customer = User::create([
            'name' => 'Ali Customer',
            'email' => 'ali@example.com',
            'password' => Hash::make('password123'),
            'mobile' => '03009876543',
            'sellerType' => 2,
            'is_verified' => true,
            'address' => 'House 123, Sector 4A',
            'address2' => 'Street 5',
            'city' => 'Karachi',
        ]);
    }

    protected function createProduct(array $globalAttrs = [], array $listingAttrs = []): ShopProduct
    {
        $global = GlobalProduct::create(array_merge([
            'catalog_category_id' => $this->category->id,
            'name' => 'Basmati Rice 5kg',
            'base_price' => 1000.00,
            'unit_type' => 'bag',
            'display_image_url' => 'products/rice.jpg',
            'is_active' => true,
        ], $globalAttrs));

        return ShopProduct::create(array_merge([
            'seller_id' => $this->seller->id,
            'global_product_id' => $global->id,
            'custom_price' => null,
            'stock_quantity' => 20,
            'is_active' => true,
        ], $listingAttrs));
    }

    protected function createRider(array $attributes = []): Rider
    {
        return Rider::create(array_merge([
            'name' => 'Rashid Rider',
            'email' => 'rashid' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03001112233',
            'cnic_number' => '42101-1234567-1',
            'address' => 'House 5, Street 2, Baldia Town, Karachi',
            'vehicle_type' => 'motorcycle',
            'vehicle_number' => 'KHI-1234',
            'status' => 'online',
            'is_approved' => true,
            'is_verified' => true,
        ], $attributes));
    }

    // ==========================================
    // 1. Cart Validation Tests
    // ==========================================

    public function test_cart_validate_ok_case(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $product = $this->createProduct(['base_price' => 500.00], ['stock_quantity' => 10]);

        $response = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                ['listing_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_valid' => true,
                    'items' => [
                        [
                            'listing_id' => $product->id,
                            'name' => 'Basmati Rice 5kg',
                            'quantity' => 2,
                            'unit_price' => '500.00',
                            'line_total' => '1000.00',
                            'available_stock' => 10,
                            'ok' => true,
                            'problem_code' => null,
                        ],
                    ],
                    'totals' => [
                        'subtotal' => '1000.00',
                        'delivery_charges' => '0.00',
                        'discount' => '0.00',
                        'total' => '1000.00',
                    ],
                ],
            ]);
    }

    public function test_cart_validate_mixed_sellers_returns_422(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $seller2 = Seller::create([
            'name' => 'Second Mart',
            'email' => 'second@example.com',
            'password' => Hash::make('password123'),
            'accountIsApproved' => 1,
            'is_deleted' => false,
            'is_open' => true,
        ]);

        $p1 = $this->createProduct();
        $p2 = ShopProduct::create([
            'seller_id' => $seller2->id,
            'global_product_id' => $p1->global_product_id,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                ['listing_id' => $p1->id, 'quantity' => 1],
                ['listing_id' => $p2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'MULTIPLE_SELLERS',
            ]);
    }

    public function test_cart_validate_closed_shop_returns_422(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $this->seller->update(['is_open' => false]);
        $product = $this->createProduct();

        $response = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                ['listing_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'SELLER_CLOSED',
            ]);
    }

    public function test_cart_validate_reports_all_problem_items_together(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $inactive = $this->createProduct([], ['is_active' => false, 'stock_quantity' => 10]);
        $outOfStock = $this->createProduct([], ['stock_quantity' => 0]);
        $limited = $this->createProduct([], ['stock_quantity' => 2]);

        $response = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                ['listing_id' => $inactive->id, 'quantity' => 1],
                ['listing_id' => $outOfStock->id, 'quantity' => 1],
                ['listing_id' => $limited->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertFalse($data['is_valid']);
        $this->assertCount(3, $data['items']);

        $item0 = $data['items'][0];
        $this->assertFalse($item0['ok']);
        $this->assertEquals('INACTIVE', $item0['problem_code']);

        $item1 = $data['items'][1];
        $this->assertFalse($item1['ok']);
        $this->assertEquals('OUT_OF_STOCK', $item1['problem_code']);

        $item2 = $data['items'][2];
        $this->assertFalse($item2['ok']);
        $this->assertEquals('LIMITED_STOCK', $item2['problem_code']);
    }

    public function test_cart_validate_rejects_duplicate_listing_ids(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct();

        $response = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                ['listing_id' => $p->id, 'quantity' => 1],
                ['listing_id' => $p->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
            ]);
    }

    public function test_client_sent_price_is_rejected_or_ignored(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct();

        $response = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                ['listing_id' => $p->id, 'quantity' => 1],
            ],
            'price' => 10,
            'total' => 10,
        ]);

        $response->assertStatus(422);
    }

    // ==========================================
    // 2. Price Rule Tests
    // ==========================================

    public function test_effective_price_equals_custom_price_or_base_price_when_null(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $pCustom = $this->createProduct(['base_price' => 200.00], ['custom_price' => 250.00]);
        $pBase = $this->createProduct(['base_price' => 300.00], ['custom_price' => null]);

        $response = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                ['listing_id' => $pCustom->id, 'quantity' => 1],
                ['listing_id' => $pBase->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(200);
        $items = $response->json('data.items');

        $this->assertEquals('250.00', $items[0]['unit_price']);
        $this->assertEquals('300.00', $items[1]['unit_price']);
        $this->assertEquals('550.00', $response->json('data.totals.subtotal'));
    }

    // ==========================================
    // 3. Promo Code Tests
    // ==========================================

    public function test_promo_percent_with_maximum_cap(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct(['base_price' => 1000.00]);

        PromoCode::create([
            'code' => 'SAVE20',
            'discount_type' => 'percent',
            'discount_value' => 20, // 20% of 1000 = 200
            'maximum_discount_amount' => 150.00, // Capped at 150
            'minimum_order_amount' => 500.00,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/customer/checkout/apply-promo', [
            'items' => [
                ['listing_id' => $p->id, 'quantity' => 1],
            ],
            'code' => 'save20', // Test lowercase handling
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'totals' => [
                        'subtotal' => '1000.00',
                        'discount' => '150.00',
                        'total' => '850.00',
                    ],
                ],
            ]);
    }

    public function test_promo_fixed_discount_and_total_never_below_zero(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct(['base_price' => 200.00]);

        PromoCode::create([
            'code' => 'FLAT500',
            'discount_type' => 'fixed',
            'discount_value' => 500.00, // Exceeds 200 subtotal
            'minimum_order_amount' => 100.00,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/customer/checkout/apply-promo', [
            'items' => [
                ['listing_id' => $p->id, 'quantity' => 1],
            ],
            'code' => 'FLAT500',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'totals' => [
                        'subtotal' => '200.00',
                        'discount' => '200.00',
                        'total' => '0.00',
                    ],
                ],
            ]);
    }

    public function test_promo_validation_errors(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct(['base_price' => 200.00]);

        // 1. Min order not met
        PromoCode::create([
            'code' => 'MIN1000',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'minimum_order_amount' => 1000.00,
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/customer/checkout/apply-promo', [
            'items' => [['listing_id' => $p->id, 'quantity' => 1]],
            'code' => 'MIN1000',
        ])->assertStatus(422)->assertJson(['code' => 'PROMO_MIN_ORDER_NOT_MET']);

        // 2. Expired
        PromoCode::create([
            'code' => 'EXPIRED',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'minimum_order_amount' => 100.00,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/customer/checkout/apply-promo', [
            'items' => [['listing_id' => $p->id, 'quantity' => 1]],
            'code' => 'EXPIRED',
        ])->assertStatus(422)->assertJson(['code' => 'PROMO_EXPIRED']);

        // 3. Not started yet
        PromoCode::create([
            'code' => 'FUTURE',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'minimum_order_amount' => 100.00,
            'starts_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/customer/checkout/apply-promo', [
            'items' => [['listing_id' => $p->id, 'quantity' => 1]],
            'code' => 'FUTURE',
        ])->assertStatus(422)->assertJson(['code' => 'PROMO_INVALID']);

        // 4. Usage limit reached
        PromoCode::create([
            'code' => 'MAXED',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'minimum_order_amount' => 100.00,
            'usage_limit' => 5,
            'used_count' => 5,
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/customer/checkout/apply-promo', [
            'items' => [['listing_id' => $p->id, 'quantity' => 1]],
            'code' => 'MAXED',
        ])->assertStatus(422)->assertJson(['code' => 'PROMO_LIMIT_REACHED']);
    }

    // ==========================================
    // 4. Place Order Tests
    // ==========================================

    public function test_place_order_success_with_totals_snapshots_and_no_stock_decrement(): void
    {
        Mail::fake();
        Notification::fake();
        Sanctum::actingAs($this->customer, ['customer']);

        $p = $this->createProduct(['base_price' => 500.00], ['stock_quantity' => 10]);

        $promo = PromoCode::create([
            'code' => 'WELCOME50',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'minimum_order_amount' => 200.00,
            'usage_limit' => 10,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $response = $this->withHeader('Idempotency-Key', 'unique-idempotency-key-12345678')
            ->postJson('/api/v1/customer/orders', [
                'items' => [
                    ['listing_id' => $p->id, 'quantity' => 2],
                ],
                'promo_code' => 'welcome50',
                'payment_method' => 'cod',
                'delivery_instructions' => 'Call on arrival',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'pending',
                    'status_step' => 1,
                    'status_label' => 'Pending',
                    'can_cancel' => true,
                    'can_review' => false,
                    'payment_method' => 'cod',
                    'subtotal' => '1000.00',
                    'delivery_charges' => '0.00',
                    'discount_amount' => '50.00',
                    'total_amount' => '950.00',
                    'promo_code' => 'WELCOME50',
                ],
            ]);

        // Verify stock was NOT decremented at placement (web reserves only on seller confirm)
        $p->refresh();
        $this->assertEquals(10, $p->stock_quantity);

        // Verify promo used_count incremented
        $promo->refresh();
        $this->assertEquals(1, $promo->used_count);

        // Verify order items snapshots
        $orderId = $response->json('data.id');
        $item = OrderItem::where('order_id', $orderId)->first();
        $this->assertNotNull($item);
        $this->assertEquals('Basmati Rice 5kg', $item->item_name);
        $this->assertEquals('500.00', $item->price);
        $this->assertEquals(2, $item->quantity);

        // Verify side effects
        Notification::assertSentTo($this->seller, NewOrderNotification::class);
        Mail::assertSent(OrderPlacedMail::class, function ($mail) {
            return $mail->hasTo('ali@example.com');
        });
    }

    public function test_place_order_succeeds_even_if_mail_throws(): void
    {
        Notification::fake();
        Sanctum::actingAs($this->customer, ['customer']);

        $p = $this->createProduct();

        // Simulate failing mailer
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP Connection Timeout'));

        $response = $this->withHeader('Idempotency-Key', 'unique-idempotency-key-mailfail')
            ->postJson('/api/v1/customer/orders', [
                'items' => [['listing_id' => $p->id, 'quantity' => 1]],
                'payment_method' => 'cod',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['user_id' => $this->customer->id, 'status' => 'pending']);
    }

    public function test_place_order_idempotent_replay(): void
    {
        Mail::fake();
        Notification::fake();
        Sanctum::actingAs($this->customer, ['customer']);

        $p = $this->createProduct();
        $key = 'idempotent-unique-test-key-99999999';

        $first = $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/customer/orders', [
                'items' => [['listing_id' => $p->id, 'quantity' => 1]],
                'payment_method' => 'cod',
            ]);

        $first->assertStatus(201);
        $firstOrderId = $first->json('data.id');

        $second = $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/customer/orders', [
                'items' => [['listing_id' => $p->id, 'quantity' => 1]],
                'payment_method' => 'cod',
            ]);

        $second->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $firstOrderId,
                ],
                'meta' => [
                    'idempotent_replay' => true,
                ],
            ]);

        // Exactly one order exists in the database
        $this->assertEquals(1, Order::where('user_id', $this->customer->id)->count());
    }

    public function test_place_order_missing_idempotency_key_returns_422(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct();

        $response = $this->postJson('/api/v1/customer/orders', [
            'items' => [['listing_id' => $p->id, 'quantity' => 1]],
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422)
            ->assertJson(['code' => 'VALIDATION_ERROR']);
    }

    public function test_place_order_unsupported_payment_method_returns_422(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct();

        $response = $this->withHeader('Idempotency-Key', 'idempotency-key-online-pay-12345')
            ->postJson('/api/v1/customer/orders', [
                'items' => [['listing_id' => $p->id, 'quantity' => 1]],
                'payment_method' => 'online',
            ]);

        $response->assertStatus(422)
            ->assertJson(['code' => 'PAYMENT_METHOD_UNSUPPORTED']);
    }

    public function test_place_order_too_many_active_orders_limit(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct();

        // Create 3 active orders
        for ($i = 0; $i < 3; $i++) {
            Order::create([
                'user_id' => $this->customer->id,
                'seller_id' => $this->seller->id,
                'address' => 'Test address',
                'phone' => '03001234567',
                'status' => 'pending',
                'total_amount' => 500.00,
            ]);
        }

        $response = $this->withHeader('Idempotency-Key', 'idempotency-key-limit-12345678')
            ->postJson('/api/v1/customer/orders', [
                'items' => [['listing_id' => $p->id, 'quantity' => 1]],
                'payment_method' => 'cod',
            ]);

        $response->assertStatus(422)
            ->assertJson(['code' => 'TOO_MANY_ACTIVE_ORDERS']);
    }

    public function test_promo_race_condition_when_last_redemption_is_consumed(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $p = $this->createProduct(['base_price' => 500.00]);

        // Promo with usage limit = 1, used_count = 0
        $promo = PromoCode::create([
            'code' => 'LASTONE',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'minimum_order_amount' => 100.00,
            'usage_limit' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);

        // First customer consumes the last redemption
        $res1 = $this->withHeader('Idempotency-Key', 'idempotency-key-race-first-order')
            ->postJson('/api/v1/customer/orders', [
                'items' => [['listing_id' => $p->id, 'quantity' => 1]],
                'promo_code' => 'LASTONE',
                'payment_method' => 'cod',
            ]);
        $res1->assertStatus(201);
        $this->assertEquals(1, $promo->fresh()->used_count);

        // Second customer order attempt with the now-exhausted promo code
        $customer2 = User::create([
            'name' => 'Second Customer',
            'email' => 'secondcust@example.com',
            'password' => Hash::make('password123'),
            'mobile' => '03009998877',
            'sellerType' => 2,
            'is_verified' => true,
            'address' => 'House 99, Sector 4A',
            'city' => 'Karachi',
        ]);
        Sanctum::actingAs($customer2, ['customer']);

        $res2 = $this->withHeader('Idempotency-Key', 'idempotency-key-race-second-order')
            ->postJson('/api/v1/customer/orders', [
                'items' => [['listing_id' => $p->id, 'quantity' => 1]],
                'promo_code' => 'LASTONE',
                'payment_method' => 'cod',
            ]);

        $res2->assertStatus(422)
            ->assertJson(['code' => 'ORDER_VALIDATION_FAILED']);

        // Verify used_count did not increment past limit
        $this->assertEquals(1, $promo->fresh()->used_count);
    }

    // ==========================================
    // 5. Ownership & Security Tests
    // ==========================================

    public function test_user_b_cannot_see_cancel_or_review_user_a_order_returns_404(): void
    {
        $userB = User::create([
            'name' => 'Bob Customer',
            'email' => 'bob@example.com',
            'password' => Hash::make('password123'),
            'sellerType' => 2,
            'is_verified' => true,
        ]);

        $orderA = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'pending',
            'total_amount' => 500.00,
        ]);

        Sanctum::actingAs($userB, ['customer']);

        // Show returns 404 NOT_FOUND
        $this->getJson("/api/v1/customer/orders/{$orderA->id}")
            ->assertStatus(404)
            ->assertJson(['code' => 'NOT_FOUND']);

        // Cancel returns 404 NOT_FOUND
        $this->postJson("/api/v1/customer/orders/{$orderA->id}/cancel")
            ->assertStatus(404)
            ->assertJson(['code' => 'NOT_FOUND']);

        // Feedback returns 404 NOT_FOUND
        $this->postJson("/api/v1/customer/orders/{$orderA->id}/feedback", [
            'rating' => 5,
            'feedback' => 'Nice order',
        ])->assertStatus(404)->assertJson(['code' => 'NOT_FOUND']);
    }

    public function test_seller_or_rider_token_cannot_call_customer_routes(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'pending',
            'total_amount' => 500.00,
        ]);

        // Acting as seller
        Sanctum::actingAs($this->seller, ['seller']);

        $this->getJson('/api/v1/customer/orders/active')
            ->assertStatus(403)
            ->assertJson(['code' => 'FORBIDDEN_ROLE']);

        $this->getJson("/api/v1/customer/orders/{$order->id}")
            ->assertStatus(403)
            ->assertJson(['code' => 'FORBIDDEN_ROLE']);

        // Acting as rider
        $rider = $this->createRider(['email' => 'rashid@example.com']);

        Sanctum::actingAs($rider, ['rider']);

        $this->getJson('/api/v1/customer/orders/active')
            ->assertStatus(403)
            ->assertJson(['code' => 'FORBIDDEN_ROLE']);
    }

    // ==========================================
    // 6. Cancel & Inventory Restoration Tests
    // ==========================================

    public function test_cancel_pending_order(): void
    {
        Mail::fake();
        Notification::fake();
        Sanctum::actingAs($this->customer, ['customer']);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'pending',
            'total_amount' => 500.00,
        ]);

        $response = $this->postJson("/api/v1/customer/orders/{$order->id}/cancel", [
            'reason' => 'Changed my mind',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'cancelled',
                    'status_step' => 0,
                    'status_label' => 'Cancelled',
                    'cancellation_reason' => 'Changed my mind',
                ],
            ]);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertNotNull($order->cancelled_at);

        Notification::assertSentTo($this->customer, OrderStatusNotification::class);
        Mail::assertSent(OrderCancelledMail::class);
    }

    public function test_cancel_confirmed_order_restores_stock_exactly_once(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $product = $this->createProduct(['base_price' => 500.00], ['stock_quantity' => 10]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'confirmed_by_seller',
            'total_amount' => 1000.00,
        ]);

        $order->forceFill(['inventory_reserved_at' => now()])->save();

        OrderItem::create([
            'order_id' => $order->id,
            'shop_product_id' => $product->id,
            'global_product_id' => $product->global_product_id,
            'item_name' => 'Basmati Rice',
            'quantity' => 3,
            'price' => 500.00,
        ]);

        // Stock was 10 (reserved 3 by seller confirmation -> pretend stock was reduced to 7)
        $product->update(['stock_quantity' => 7]);

        // First cancel: restores 3 back to 10
        $response = $this->postJson("/api/v1/customer/orders/{$order->id}/cancel");
        $response->assertStatus(200);

        $product->refresh();
        $this->assertEquals(10, $product->stock_quantity);

        $order->refresh();
        $this->assertNull($order->inventory_reserved_at);
        $this->assertEquals('cancelled', $order->status);

        // Second cancel attempt: rejected with 422, does NOT double restore
        $response2 = $this->postJson("/api/v1/customer/orders/{$order->id}/cancel");
        $response2->assertStatus(422)
            ->assertJson(['code' => 'ORDER_NOT_CANCELLABLE']);

        $product->refresh();
        $this->assertEquals(10, $product->stock_quantity);
    }

    public function test_cancel_preparing_order_is_rejected(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'preparing',
            'total_amount' => 500.00,
        ]);

        $this->postJson("/api/v1/customer/orders/{$order->id}/cancel")
            ->assertStatus(422)
            ->assertJson(['code' => 'ORDER_NOT_CANCELLABLE']);
    }

    // ==========================================
    // 7. Feedback & Review Tests
    // ==========================================

    public function test_feedback_allowed_only_for_delivered_or_completed_orders(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $pendingOrder = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'pending',
            'total_amount' => 500.00,
        ]);

        $this->postJson("/api/v1/customer/orders/{$pendingOrder->id}/feedback", [
            'rating' => 5,
            'feedback' => 'Great product!',
        ])->assertStatus(422)->assertJson(['code' => 'ORDER_NOT_REVIEWABLE']);

        $completedOrder = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'completed',
            'total_amount' => 500.00,
        ]);

        $response = $this->postJson("/api/v1/customer/orders/{$completedOrder->id}/feedback", [
            'rating' => 4,
            'feedback' => '<b>Fast</b> delivery!',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        // Feedback saved with status pending and HTML stripped
        $feedback = Feedback::where('order_id', $completedOrder->id)->first();
        $this->assertNotNull($feedback);
        $this->assertEquals('pending', $feedback->status);
        $this->assertEquals('Fast delivery!', $feedback->feedback);
        $this->assertEquals(4, $feedback->rating);

        // Second feedback rejected with ALREADY_REVIEWED
        $this->postJson("/api/v1/customer/orders/{$completedOrder->id}/feedback", [
            'rating' => 5,
            'feedback' => 'Second review',
        ])->assertStatus(422)->assertJson(['code' => 'ALREADY_REVIEWED']);
    }

    // ==========================================
    // 8. Privacy & Sensitive Field Filtering Tests
    // ==========================================

    public function test_order_responses_never_contain_sensitive_information(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $product = $this->createProduct();

        $rider = $this->createRider(['email' => 'tariq@example.com', 'name' => 'Tariq Rider']);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'House A',
            'phone' => '03001234567',
            'status' => 'delivered',
            'total_amount' => 1000.00,
        ]);
        $order->forceFill([
            'rider_id' => $rider->id,
            'delivery_proof_image' => 'delivery_proofs/proof1.jpg',
        ])->save();

        $response = $this->getJson("/api/v1/customer/orders/{$order->id}");
        $response->assertStatus(200);

        $jsonString = $response->getContent();

        $this->assertStringNotContainsString('password', $jsonString);
        $this->assertStringNotContainsString('remember_token', $jsonString);
        $this->assertStringNotContainsString('almadina@example.com', $jsonString); // seller email
        $this->assertStringNotContainsString('42101-1234567-1', $jsonString); // rider CNIC
        $this->assertStringNotContainsString('tariq@example.com', $jsonString); // rider email

        // Delivery proof must be full URL
        $this->assertStringContainsString('http', $response->json('data.delivery_proof_image'));
    }

    // ==========================================
    // 9. Query Count Optimization Tests
    // ==========================================

    public function test_active_orders_query_count_is_bounded(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        for ($i = 0; $i < 5; $i++) {
            $ord = Order::create([
                'user_id' => $this->customer->id,
                'seller_id' => $this->seller->id,
                'address' => 'Address ' . $i,
                'phone' => '03001234567',
                'status' => 'pending',
                'total_amount' => 500.00,
            ]);

            OrderItem::create([
                'order_id' => $ord->id,
                'item_name' => 'Item ' . $i,
                'quantity' => 1,
                'price' => 500.00,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson('/api/v1/customer/orders/active');
        $response->assertStatus(200);

        $queryCount = count(DB::getQueryLog());
        // Should execute ~4-6 queries: user token check, count query, paginated orders query, seller eager load, items eager load
        $this->assertLessThanOrEqual(8, $queryCount, "Active orders executed {$queryCount} queries (possible N+1)");
    }

    public function test_order_show_query_count_is_bounded(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'Address',
            'phone' => '03001234567',
            'status' => 'pending',
            'total_amount' => 500.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => 'Item',
            'quantity' => 1,
            'price' => 500.00,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson("/api/v1/customer/orders/{$order->id}");
        $response->assertStatus(200);

        $queryCount = count(DB::getQueryLog());
        // Should execute ~4-6 queries: auth user, order with seller/items/rider eager load, feedback exists check
        $this->assertLessThanOrEqual(6, $queryCount, "Show order executed {$queryCount} queries");
    }
}
