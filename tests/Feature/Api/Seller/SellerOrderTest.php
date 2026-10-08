<?php

namespace Tests\Feature\Api\Seller;

use App\Events\OrderReadyForPickup;
use App\Mail\OrderCancelledMail;
use App\Models\CatalogCategory;
use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Seller $seller;
    protected Seller $seller2;
    protected User $customer;
    protected Rider $rider;
    protected CatalogCategory $category;
    protected GlobalProduct $globalProduct1;
    protected GlobalProduct $globalProduct2;
    protected ShopProduct $shopProduct1;
    protected ShopProduct $shopProduct2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = CatalogCategory::create([
            'name' => 'Supermarket',
            'slug' => 'supermarket',
            'is_active' => true,
        ]);

        $this->seller = Seller::create([
            'name' => 'Al-Madina Mart',
            'email' => 'madina@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'area' => 'Gulshan',
            'sector' => 'Block 5',
            'near_areas' => ['Disco Bakery'],
            'full_address' => 'Shop 12, Block 5, Gulshan, Karachi',
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
            'is_open' => true,
            'catalog_category_id' => $this->category->id,
            'accountIsApproved' => 1,
            'is_deleted' => 0,
        ]);

        $this->seller2 = Seller::create([
            'name' => 'Bismillah Store',
            'email' => 'bismillah@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'area' => 'Clifton',
            'sector' => 'Block 2',
            'full_address' => 'Shop 4, Block 2, Clifton, Karachi',
            'is_open' => true,
            'catalog_category_id' => $this->category->id,
            'accountIsApproved' => 1,
            'is_deleted' => 0,
        ]);

        $this->customer = User::create([
            'name' => 'Zainab Customer',
            'email' => 'zainab@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03001234567',
            'city' => 'Karachi',
            'sellerType' => 0,
            'is_verified' => true,
        ]);

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

        $this->globalProduct1 = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'Olpers Milk 1L',
            'slug' => 'olpers-milk-1l',
            'base_price' => 280.00,
            'is_active' => true,
        ]);

        $this->globalProduct2 = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'Dawn Bread Large',
            'slug' => 'dawn-bread-large',
            'base_price' => 160.00,
            'is_active' => true,
        ]);

        $this->shopProduct1 = ShopProduct::create([
            'seller_id' => $this->seller->id,
            'global_product_id' => $this->globalProduct1->id,
            'custom_price' => 280.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->shopProduct2 = ShopProduct::create([
            'seller_id' => $this->seller->id,
            'global_product_id' => $this->globalProduct2->id,
            'custom_price' => 160.00,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
    }

    protected function createOrder(Seller $seller, array $attributes = [], array $items = []): Order
    {
        $fillableKeys = ['user_id', 'seller_id', 'address', 'phone', 'status', 'total_amount', 'transaction_id'];
        $fillable = [
            'user_id' => $this->customer->id,
            'seller_id' => $seller->id,
            'address' => 'Flat 402, Al-Razi Heights, Sector F-8, Islamabad',
            'phone' => '03001234567',
            'status' => 'pending',
            'total_amount' => 560.00,
        ];

        $nonFillable = [
            'delivery_charges' => 0.00,
            'discount_amount' => 0.00,
            'delivery_instructions' => 'Call on arrival.',
        ];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $fillableKeys, true)) {
                $fillable[$key] = $value;
            } else {
                $nonFillable[$key] = $value;
            }
        }

        $order = Order::create($fillable);

        if (! empty($nonFillable)) {
            DB::table('orders')->where('id', $order->id)->update($nonFillable);
            $order->refresh();
        }

        if (empty($items)) {
            OrderItem::create([
                'order_id' => $order->id,
                'shop_product_id' => $this->shopProduct1->id,
                'global_product_id' => $this->globalProduct1->id,
                'item_name' => 'Olpers Milk 1L',
                'unit_type' => 'piece',
                'quantity' => 2,
                'price' => 280.00,
            ]);
        } else {
            foreach ($items as $item) {
                OrderItem::create(array_merge(['order_id' => $order->id], $item));
            }
        }

        return $order->refresh();
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/v1/seller/dashboard')->assertStatus(401);
        $this->getJson('/api/v1/seller/orders')->assertStatus(401);
        $this->getJson('/api/v1/seller/operating-hours')->assertStatus(401);
        $this->getJson('/api/v1/seller/earnings')->assertStatus(401);
    }

    public function test_non_seller_tokens_are_forbidden(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $this->getJson('/api/v1/seller/dashboard')->assertStatus(403);

        Sanctum::actingAs($this->rider, ['rider']);
        $this->getJson('/api/v1/seller/dashboard')->assertStatus(403);
    }

    public function test_unapproved_or_deleted_seller_is_rejected(): void
    {
        // Unapproved seller
        $this->seller->update(['accountIsApproved' => 0]);
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->getJson('/api/v1/seller/dashboard');
        $res->assertStatus(403)
            ->assertJsonPath('code', 'SELLER_ACCOUNT_INACTIVE');

        // Soft-deleted seller
        $this->seller->update(['accountIsApproved' => 1, 'is_deleted' => 1]);
        $resDeleted = $this->getJson('/api/v1/seller/dashboard');
        $resDeleted->assertStatus(403)
            ->assertJsonPath('code', 'SELLER_ACCOUNT_INACTIVE');
    }

    public function test_seller_dashboard_returns_profile_badges_and_today_sales(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Create orders in different states
        $this->createOrder($this->seller, ['status' => 'pending']);
        $this->createOrder($this->seller, ['status' => 'confirmed_by_seller']);
        $this->createOrder($this->seller, ['status' => 'preparing']);
        $this->createOrder($this->seller, ['status' => 'ready_for_pickup']);
        $this->createOrder($this->seller, ['status' => 'completed', 'total_amount' => 1200.00]);
        $this->createOrder($this->seller, ['status' => 'cancelled']);
        $this->createOrder($this->seller, ['status' => 'rejected']);

        $res = $this->getJson('/api/v1/seller/dashboard');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.suggested_poll_seconds', 15)
            ->assertJsonPath('data.store.name', 'Al-Madina Mart')
            ->assertJsonPath('data.store.is_open', true)
            ->assertJsonPath('data.order_badges.pending', 1)
            ->assertJsonPath('data.order_badges.confirmed', 1)
            ->assertJsonPath('data.order_badges.preparing', 1)
            ->assertJsonPath('data.order_badges.ready', 1)
            ->assertJsonPath('data.order_badges.completed', 1)
            ->assertJsonPath('data.order_badges.cancelled', 1)
            ->assertJsonPath('data.order_badges.rejected', 1)
            ->assertJsonPath('data.order_badges.active_total', 4)
            ->assertJsonPath('data.today.sales', '1200.00')
            ->assertJsonPath('data.today.orders_count', 7);
        $this->assertNotNull($res->json('meta.server_time'));
    }

    public function test_seller_orders_list_groups_sorting_and_pending_count(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $order1 = $this->createOrder($this->seller, ['status' => 'pending', 'created_at' => Carbon::now()->subMinutes(10)]);
        $order2 = $this->createOrder($this->seller, ['status' => 'pending', 'created_at' => Carbon::now()->subMinutes(5)]);
        $order3 = $this->createOrder($this->seller, ['status' => 'confirmed_by_seller', 'created_at' => Carbon::now()->subMinutes(2)]);

        // 1. Group = pending (sorted oldest first)
        $resPending = $this->getJson('/api/v1/seller/orders?group=pending');
        $resPending->assertStatus(200)
            ->assertJsonPath('meta.pending_count', 2)
            ->assertJsonPath('meta.suggested_poll_seconds', 15)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $order1->id)
            ->assertJsonPath('data.0.customer_first_name', 'Zainab')
            ->assertJsonPath('data.1.id', $order2->id);
        $this->assertNotNull($resPending->json('meta.server_time'));

        // Assert customer_name and customer_phone are redacted from list resource
        $this->assertArrayNotHasKey('customer_name', $resPending->json('data.0'));
        $this->assertArrayNotHasKey('customer_phone', $resPending->json('data.0'));

        // 2. Group = active
        $resActive = $this->getJson('/api/v1/seller/orders?group=active');
        $resActive->assertStatus(200)
            ->assertJsonPath('meta.pending_count', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order3->id);

        // 3. Status filter
        $resFilter = $this->getJson('/api/v1/seller/orders?status=confirmed_by_seller');
        $resFilter->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order3->id);
    }

    public function test_seller_orders_isolation_other_seller_orders_not_visible(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $myOrder = $this->createOrder($this->seller, ['status' => 'pending']);
        $otherOrder = $this->createOrder($this->seller2, ['status' => 'pending']);

        $res = $this->getJson('/api/v1/seller/orders');
        $res->assertStatus(200);

        $ids = collect($res->json('data'))->pluck('id')->all();
        $this->assertContains($myOrder->id, $ids);
        $this->assertNotContains($otherOrder->id, $ids);

        // Detail endpoint returns 404 for other seller's order
        $this->getJson("/api/v1/seller/orders/{$otherOrder->id}")
            ->assertStatus(404);
    }

    public function test_seller_order_detail_shows_full_info_items_stock_and_allowed_actions(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, [
            'status' => 'pending',
            'delivery_instructions' => 'Gate code #1234',
        ]);

        $res = $this->getJson("/api/v1/seller/orders/{$order->id}");
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.status_label', 'Pending')
            ->assertJsonPath('data.allowed_actions', ['confirm', 'reject'])
            ->assertJsonPath('data.customer.name', 'Zainab Customer')
            ->assertJsonPath('data.customer.phone', '03001234567')
            ->assertJsonPath('data.customer.delivery_instructions', 'Gate code #1234')
            ->assertJsonPath('data.items.0.item_name', 'Olpers Milk 1L')
            ->assertJsonPath('data.items.0.current_stock', 10)
            ->assertJsonPath('data.items.0.is_catalog_item', true)
            ->assertJsonPath('data.total_amount', '560.00');
    }

    public function test_confirm_order_atomically_reserves_stock_and_stamps_inventory_reserved(): void
    {
        Notification::fake();
        Sanctum::actingAs($this->seller, ['seller']);

        $this->assertEquals(10, $this->shopProduct1->fresh()->stock_quantity);

        $order = $this->createOrder($this->seller, ['status' => 'pending']);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/confirm");
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'confirmed_by_seller')
            ->assertJsonPath('data.allowed_actions', ['prepare', 'reject']);

        // Stock decreased by 2 (from 10 to 8)
        $this->assertEquals(8, $this->shopProduct1->fresh()->stock_quantity);

        // inventory_reserved_at is stamped
        $this->assertNotNull($order->fresh()->inventory_reserved_at);

        Notification::assertSentTo($this->customer, OrderStatusNotification::class);
    }

    public function test_confirm_order_fails_atomically_on_insufficient_stock_and_rolls_back(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // ShopProduct 2 has stock 5. Create an order wanting 8
        $order = $this->createOrder($this->seller, ['status' => 'pending'], [
            [
                'shop_product_id' => $this->shopProduct1->id,
                'item_name' => 'Olpers Milk 1L',
                'quantity' => 2, // Stock 10 -> sufficient
                'price' => 280.00,
            ],
            [
                'shop_product_id' => $this->shopProduct2->id,
                'item_name' => 'Dawn Bread Large',
                'quantity' => 8, // Stock 5 -> INSUFFICIENT
                'price' => 160.00,
            ],
        ]);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/confirm");
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['stock']);

        // Check atomic rollback: neither product stock was decremented!
        $this->assertEquals(10, $this->shopProduct1->fresh()->stock_quantity);
        $this->assertEquals(5, $this->shopProduct2->fresh()->stock_quantity);

        // Order remains pending
        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->inventory_reserved_at);
    }

    public function test_confirm_order_ignores_legacy_products_without_shop_product_id(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $legacyProduct = \App\Models\Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Legacy Vegetable Pack',
            'description' => 'Fresh vegetables',
            'seller_city' => 'Karachi',
            'seller_area' => 'Gulshan',
            'seller_contact_no' => '03331234567',
            'price' => 150.00,
            'stock_quantity' => 10,
            'unit_type' => 'pack',
            'category_id' => $this->category->id,
            'is_approved' => 1,
        ]);

        $order = $this->createOrder($this->seller, ['status' => 'pending'], [
            [
                'product_id' => $legacyProduct->id,
                'shop_product_id' => null,
                'item_name' => 'Legacy Vegetable Pack',
                'quantity' => 3,
                'price' => 150.00,
            ],
        ]);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/confirm");
        $res->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed_by_seller');
    }

    public function test_confirm_order_rejects_non_pending_orders(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'confirmed_by_seller']);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/confirm");
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_prepare_order_transitions_confirmed_to_preparing(): void
    {
        Notification::fake();
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'confirmed_by_seller']);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/prepare");
        $res->assertStatus(200)
            ->assertJsonPath('data.status', 'preparing')
            ->assertJsonPath('data.allowed_actions', ['ready', 'reject']);

        $this->assertEquals('preparing', $order->fresh()->status);
        Notification::assertSentTo($this->customer, OrderStatusNotification::class);
    }

    public function test_ready_order_transitions_and_broadcasts_order_ready_for_pickup(): void
    {
        Event::fake([OrderReadyForPickup::class]);
        Notification::fake();
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'preparing']);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/ready");
        $res->assertStatus(200)
            ->assertJsonPath('data.status', 'ready_for_pickup');

        $this->assertEquals('ready_for_pickup', $order->fresh()->status);
        Event::assertDispatched(OrderReadyForPickup::class);
        Notification::assertSentTo($this->customer, OrderStatusNotification::class);
    }

    public function test_reject_order_restores_reserved_stock_and_sends_email(): void
    {
        Mail::fake();
        Notification::fake();
        Sanctum::actingAs($this->seller, ['seller']);

        // Decrement stock as if confirmed
        $this->shopProduct1->update(['stock_quantity' => 8]);

        $order = $this->createOrder($this->seller, [
            'status' => 'confirmed_by_seller',
            'inventory_reserved_at' => now(),
        ]);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/reject", [
            'reason' => 'Store is closing early today.',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.cancellation_reason', 'Store is closing early today.');

        // Stock restored back to 10
        $this->assertEquals(10, $this->shopProduct1->fresh()->stock_quantity);
        $this->assertNull($order->fresh()->inventory_reserved_at);
        $this->assertEquals('rejected', $order->fresh()->status);

        Notification::assertSentTo($this->customer, OrderStatusNotification::class);
        Mail::assertSent(OrderCancelledMail::class);
    }

    public function test_reject_order_cannot_reject_delivered_or_completed_order(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'delivered']);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/reject");
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_complete_order_transitions_delivered_to_completed(): void
    {
        Notification::fake();
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'delivered']);

        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/complete");
        $res->assertStatus(200)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.allowed_actions', []);

        $this->assertEquals('completed', $order->fresh()->status);
        Notification::assertSentTo($this->customer, OrderStatusNotification::class);
    }

    public function test_operating_hours_get_and_put_with_overnight_logic(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // 1. GET operating hours
        $res = $this->getJson('/api/v1/seller/operating-hours');
        $res->assertStatus(200)
            ->assertJsonPath('data.is_open', true)
            ->assertJsonPath('data.opens_at', '08:00')
            ->assertJsonPath('data.closes_at', '23:00');
        $this->assertArrayHasKey('is_currently_open', $res->json('data'));

        // 2. PUT operating hours (overnight: 18:00 to 04:00)
        $putRes = $this->putJson('/api/v1/seller/operating-hours', [
            'is_open' => true,
            'opens_at' => '18:00',
            'closes_at' => '04:00',
        ]);
        $putRes->assertStatus(200)
            ->assertJsonPath('data.opens_at', '18:00')
            ->assertJsonPath('data.closes_at', '04:00');

        $this->assertEquals('18:00', substr((string) $this->seller->fresh()->opens_at, 0, 5));
        $this->assertEquals('04:00', substr((string) $this->seller->fresh()->closes_at, 0, 5));
    }

    public function test_operating_hours_validation_errors(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->putJson('/api/v1/seller/operating-hours', [
            'is_open' => 'not-a-bool',
            'opens_at' => '25:99', // Invalid time
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['is_open', 'opens_at']);
    }

    public function test_seller_earnings_summary_and_6_month_chart_with_discounts_note(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Completed order today
        $this->createOrder($this->seller, [
            'status' => 'completed',
            'total_amount' => 500.00,
            'created_at' => Carbon::now(),
        ]);

        // Delivered order 2 months ago
        $this->createOrder($this->seller, [
            'status' => 'delivered',
            'total_amount' => 800.00,
            'created_at' => Carbon::now()->subMonths(2),
        ]);

        $res = $this->getJson('/api/v1/seller/earnings');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.today', '500.00')
            ->assertJsonPath('data.summary.all_time', '1300.00')
            ->assertJsonPath('data.summary.total_completed_orders', 2)
            ->assertJsonCount(6, 'data.monthly_chart');

        $this->assertStringContainsString('promo discounts', (string) $res->json('data.discounts_note'));
    }

    public function test_end_to_end_full_order_lifecycle(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // 1. Initial pending order
        $order = $this->createOrder($this->seller, ['status' => 'pending']);

        // 2. Seller confirms order (reserves inventory)
        $this->postJson("/api/v1/seller/orders/{$order->id}/confirm")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed_by_seller');

        // 3. Seller prepares order
        $this->postJson("/api/v1/seller/orders/{$order->id}/prepare")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'preparing');

        // 4. Seller marks ready for pickup
        $this->postJson("/api/v1/seller/orders/{$order->id}/ready")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'ready_for_pickup');

        // 5. Rider accepts, picks up, and delivers order
        Sanctum::actingAs($this->rider, ['rider']);
        $this->postJson("/api/v1/rider/orders/{$order->id}/accept")->assertStatus(200);
        $this->postJson("/api/v1/rider/orders/{$order->id}/pickup")->assertStatus(200);
        $this->postJson("/api/v1/rider/orders/{$order->id}/deliver")->assertStatus(200);

        // 6. Seller completes delivered order
        Sanctum::actingAs($this->seller, ['seller']);
        $this->postJson("/api/v1/seller/orders/{$order->id}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        // 7. Customer submits review/feedback
        Sanctum::actingAs($this->customer, ['customer']);
        $feedRes = $this->postJson("/api/v1/customer/orders/{$order->id}/feedback", [
            'rating' => 5,
            'feedback' => 'Fresh products and super fast fulfillment!',
        ]);
        $feedRes->assertStatus(201);
    }

    public function test_race_customer_cancels_before_seller_confirms_and_reverse_seller_confirmed_then_customer_cancels(): void
    {
        // 1. Customer cancels pending order, then seller tries to confirm
        $order1 = $this->createOrder($this->seller, ['status' => 'pending']);
        $initialStock = $this->shopProduct1->fresh()->stock_quantity; // 10

        Sanctum::actingAs($this->customer, ['customer']);
        $this->postJson("/api/v1/customer/orders/{$order1->id}/cancel")
            ->assertStatus(200);
        $this->assertEquals('cancelled', $order1->fresh()->status);

        Sanctum::actingAs($this->seller, ['seller']);
        $this->postJson("/api/v1/seller/orders/{$order1->id}/confirm")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Stock remains untouched
        $this->assertEquals($initialStock, $this->shopProduct1->fresh()->stock_quantity);

        // 2. Reverse: Seller confirms order (decrements stock), then customer cancels (restores stock exactly once)
        $order2 = $this->createOrder($this->seller, ['status' => 'pending']);
        Sanctum::actingAs($this->seller, ['seller']);
        $this->postJson("/api/v1/seller/orders/{$order2->id}/confirm")
            ->assertStatus(200);

        // Stock decremented by 2
        $this->assertEquals($initialStock - 2, $this->shopProduct1->fresh()->stock_quantity);
        $this->assertNotNull($order2->fresh()->inventory_reserved_at);

        // Customer cancels confirmed order
        Sanctum::actingAs($this->customer, ['customer']);
        $this->postJson("/api/v1/customer/orders/{$order2->id}/cancel")
            ->assertStatus(200);

        // Stock restored back to initial number exactly once
        $this->assertEquals($initialStock, $this->shopProduct1->fresh()->stock_quantity);
        $this->assertEquals('cancelled', $order2->fresh()->status);
        $this->assertNull($order2->fresh()->inventory_reserved_at);
    }

    public function test_double_reject_returns_422_and_does_not_restore_stock_twice(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Confirm order first to reserve stock
        $order = $this->createOrder($this->seller, ['status' => 'pending']);
        $this->postJson("/api/v1/seller/orders/{$order->id}/confirm")->assertStatus(200);
        $this->assertEquals(8, $this->shopProduct1->fresh()->stock_quantity);

        // First reject restores stock
        $res1 = $this->postJson("/api/v1/seller/orders/{$order->id}/reject", ['reason' => 'First reject']);
        $res1->assertStatus(200);
        $this->assertEquals(10, $this->shopProduct1->fresh()->stock_quantity);

        // Second reject fails with 422
        $res2 = $this->postJson("/api/v1/seller/orders/{$order->id}/reject", ['reason' => 'Second reject']);
        $res2->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Stock is NOT incremented twice
        $this->assertEquals(10, $this->shopProduct1->fresh()->stock_quantity);
    }

    public function test_confirm_order_succeeds_even_when_notification_throws(): void
    {
        Notification::shouldReceive('send')->andThrow(new \Exception('Notification delivery failed'));
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'pending']);
        $res = $this->postJson("/api/v1/seller/orders/{$order->id}/confirm");

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'confirmed_by_seller');

        $this->assertEquals('confirmed_by_seller', $order->fresh()->status);
        $this->assertEquals(8, $this->shopProduct1->fresh()->stock_quantity);
    }

    public function test_setting_is_open_false_makes_shop_show_closed_and_cart_validate_return_seller_closed(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // 1. Seller closes shop
        $this->putJson('/api/v1/seller/operating-hours', [
            'is_open' => false,
            'opens_at' => '08:00',
            'closes_at' => '23:00',
        ])->assertStatus(200)
          ->assertJsonPath('data.is_open', false)
          ->assertJsonPath('data.is_currently_open', false);

        // 2. Customer browsing shop detail reflects accepting_orders: false
        Sanctum::actingAs($this->customer, ['customer']);
        $shopRes = $this->getJson("/api/v1/customer/sellers/{$this->seller->id}");
        $shopRes->assertStatus(200)
            ->assertJsonPath('data.seller.is_open', false)
            ->assertJsonPath('data.seller.accepting_orders', false);

        // 3. Customer cart validation fails with SELLER_CLOSED
        $cartRes = $this->postJson('/api/v1/customer/cart/validate', [
            'seller_id' => $this->seller->id,
            'items' => [
                [
                    'listing_id' => $this->shopProduct1->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $cartRes->assertStatus(422)
            ->assertJsonPath('code', 'SELLER_CLOSED');
    }

    public function test_query_count_for_seller_orders_and_order_detail_is_bounded(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Create 5 orders with items
        for ($i = 0; $i < 5; $i++) {
            $this->createOrder($this->seller, ['status' => 'pending']);
        }

        // Bounded queries on orders list
        DB::enableQueryLog();
        $res = $this->getJson('/api/v1/seller/orders?per_page=10');
        $res->assertStatus(200);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(10, count($queries));

        // Bounded queries on single order detail
        $singleOrder = Order::where('seller_id', $this->seller->id)->first();
        DB::flushQueryLog();
        DB::enableQueryLog();
        $detailRes = $this->getJson("/api/v1/seller/orders/{$singleOrder->id}");
        $detailRes->assertStatus(200);
        $detailQueries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(10, count($detailQueries));
    }

    public function test_seller_responses_never_contain_sensitive_information(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'pending']);

        $endpoints = [
            '/api/v1/seller/dashboard',
            '/api/v1/seller/orders',
            "/api/v1/seller/orders/{$order->id}",
            '/api/v1/seller/operating-hours',
            '/api/v1/seller/earnings',
        ];

        foreach ($endpoints as $endpoint) {
            $res = $this->getJson($endpoint);
            $res->assertStatus(200);
            $content = $res->getContent();

            $this->assertStringNotContainsString('password', $content);
            $this->assertStringNotContainsString('remember_token', $content);
            $this->assertStringNotContainsString('otp', $content);
            $this->assertStringNotContainsString($this->customer->email, $content);
            $this->assertStringNotContainsString('42101-1234567-1', $content); // Rider CNIC
            $this->assertStringNotContainsString('storage/app/', $content);
            $this->assertStringNotContainsString('public/delivery_proofs', $content);
        }
    }

    public function test_raw_body_meta_is_empty_object_on_seller_endpoints(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $order = $this->createOrder($this->seller, ['status' => 'pending']);

        // 1. GET order detail (meta is empty)
        $resShow = $this->getJson("/api/v1/seller/orders/{$order->id}");
        $resShow->assertStatus(200);
        $this->assertStringContainsString('"meta":{}', $resShow->getContent());
        $this->assertStringNotContainsString('"meta":[]', $resShow->getContent());

        // 2. GET operating hours (meta is empty)
        $resHours = $this->getJson('/api/v1/seller/operating-hours');
        $resHours->assertStatus(200);
        $this->assertStringContainsString('"meta":{}', $resHours->getContent());
        $this->assertStringNotContainsString('"meta":[]', $resHours->getContent());

        // 3. POST confirm order (meta is empty)
        $resConfirm = $this->postJson("/api/v1/seller/orders/{$order->id}/confirm");
        $resConfirm->assertStatus(200);
        $this->assertStringContainsString('"meta":{}', $resConfirm->getContent());
        $this->assertStringNotContainsString('"meta":[]', $resConfirm->getContent());
    }
}
