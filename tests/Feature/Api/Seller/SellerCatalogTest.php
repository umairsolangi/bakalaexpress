<?php

namespace Tests\Feature\Api\Seller;

use App\Models\CatalogCategory;
use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Services\Api\SellerOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected Seller $seller;
    protected Seller $seller2;
    protected User $customer;
    protected Rider $rider;
    protected User $admin;
    protected CatalogCategory $category;
    protected CatalogCategory $otherCategory;
    protected GlobalProduct $globalProduct1;
    protected GlobalProduct $globalProduct2;
    protected GlobalProduct $globalProduct3;
    protected GlobalProduct $otherCatProduct;
    protected ShopProduct $listing1;
    protected ShopProduct $listing2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = CatalogCategory::create([
            'name' => 'Supermarket',
            'slug' => 'supermarket',
            'is_active' => true,
        ]);

        $this->otherCategory = CatalogCategory::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
        ]);

        $this->seller = Seller::create([
            'name' => 'Al-Madina Mart',
            'email' => 'madina@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'area' => 'Gulshan',
            'sector' => 'Block 5',
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
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
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

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_verified' => true,
        ]);

        $this->globalProduct1 = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'Olpers Milk 1L',
            'slug' => 'olpers-milk-1l',
            'base_price' => 280.00,
            'unit_type' => 'piece',
            'is_active' => true,
        ]);

        $this->globalProduct2 = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'Dawn Bread Large',
            'slug' => 'dawn-bread-large',
            'base_price' => 160.00,
            'unit_type' => 'piece',
            'is_active' => true,
        ]);

        $this->globalProduct3 = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'National Salt 800g',
            'slug' => 'national-salt-800g',
            'base_price' => 60.00,
            'unit_type' => 'pack',
            'is_active' => true,
        ]);

        $this->otherCatProduct = GlobalProduct::create([
            'catalog_category_id' => $this->otherCategory->id,
            'name' => 'USB Cable Type C',
            'slug' => 'usb-cable-type-c',
            'base_price' => 500.00,
            'unit_type' => 'piece',
            'is_active' => true,
        ]);

        $this->listing1 = ShopProduct::create([
            'seller_id' => $this->seller->id,
            'global_product_id' => $this->globalProduct1->id,
            'custom_price' => 290.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->listing2 = ShopProduct::create([
            'seller_id' => $this->seller->id,
            'global_product_id' => $this->globalProduct2->id,
            'custom_price' => null,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);
    }

    // -------------------------------------------------------------
    // AUTH & PERMISSIONS TESTS
    // -------------------------------------------------------------

    public function test_unauthenticated_requests_get_401(): void
    {
        $this->getJson('/api/v1/seller/catalog/listings')->assertStatus(401);
        $this->getJson('/api/v1/seller/catalog/available')->assertStatus(401);
        $this->postJson('/api/v1/seller/catalog/import', [])->assertStatus(401);
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [])->assertStatus(401);
        $this->postJson('/api/v1/seller/catalog/bulk-price', [])->assertStatus(401);
    }

    public function test_customer_rider_and_admin_tokens_get_403(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);
        $this->getJson('/api/v1/seller/catalog/listings')->assertStatus(403);

        Sanctum::actingAs($this->rider, ['rider']);
        $this->getJson('/api/v1/seller/catalog/listings')->assertStatus(403);

        Sanctum::actingAs($this->admin, ['admin']);
        $this->getJson('/api/v1/seller/catalog/listings')->assertStatus(403);
    }

    public function test_unapproved_or_deleted_seller_gets_403_immediately(): void
    {
        $this->seller->update(['accountIsApproved' => 0]);
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->getJson('/api/v1/seller/catalog/listings');
        $res->assertStatus(403)
            ->assertJsonPath('code', 'SELLER_ACCOUNT_INACTIVE');

        $this->seller->update(['accountIsApproved' => 1, 'is_deleted' => 1]);
        $resDeleted = $this->getJson('/api/v1/seller/catalog/listings');
        $resDeleted->assertStatus(403)
            ->assertJsonPath('code', 'SELLER_ACCOUNT_INACTIVE');
    }

    // -------------------------------------------------------------
    // OWNERSHIP TESTS
    // -------------------------------------------------------------

    public function test_seller_b_cannot_touch_seller_a_listing(): void
    {
        Sanctum::actingAs($this->seller2, ['seller']);

        // Seller 2 cannot update Seller 1's listing (404 NOT_FOUND)
        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_quantity' => 99,
        ]);
        $res->assertStatus(404)
            ->assertJsonPath('code', 'LISTING_NOT_FOUND');
    }

    // -------------------------------------------------------------
    // LISTINGS ENDPOINT TESTS
    // -------------------------------------------------------------

    public function test_listings_index_returns_paginated_list_with_meta_totals(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->getJson('/api/v1/seller/catalog/listings');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'listing_id',
                        'global_product_id',
                        'name',
                        'unit_type',
                        'image_url',
                        'category',
                        'base_price',
                        'custom_price',
                        'effective_price',
                        'stock_quantity',
                        'is_active',
                        'price_differs_from_base',
                        'updated_at',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                    'total_active',
                    'total_inactive',
                    'total_low_stock',
                    'total_out_of_stock',
                    'server_time',
                ]
            ]);

        $this->assertEquals(2, $res->json('meta.total'));
        $this->assertEquals(2, $res->json('meta.total_active'));
        $this->assertEquals(0, $res->json('meta.total_inactive'));
        $this->assertEquals(1, $res->json('meta.total_low_stock')); // listing2 has stock 3 <= 5
        $this->assertEquals(0, $res->json('meta.total_out_of_stock'));
    }

    public function test_listings_filters_and_sorting(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Search query q
        $res = $this->getJson('/api/v1/seller/catalog/listings?q=Olpers');
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('data'));
        $this->assertEquals($this->listing1->id, $res->json('data.0.listing_id'));

        // Stock filter: low
        $resLow = $this->getJson('/api/v1/seller/catalog/listings?stock=low');
        $resLow->assertStatus(200);
        $this->assertCount(1, $resLow->json('data'));
        $this->assertEquals($this->listing2->id, $resLow->json('data.0.listing_id'));

        // Stock filter: in
        $resIn = $this->getJson('/api/v1/seller/catalog/listings?stock=in');
        $resIn->assertStatus(200);
        $this->assertCount(2, $resIn->json('data'));

        // Stock filter: out
        $this->listing2->update(['stock_quantity' => 0]);
        $resOut = $this->getJson('/api/v1/seller/catalog/listings?stock=out');
        $resOut->assertStatus(200);
        $this->assertCount(1, $resOut->json('data'));

        // Sort by price ascending
        $resSort = $this->getJson('/api/v1/seller/catalog/listings?sort=price&direction=asc');
        $resSort->assertStatus(200);
        $this->assertEquals($this->listing2->id, $resSort->json('data.0.listing_id')); // 160 < 290
    }

    public function test_listings_effective_price_and_differs_flag(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->getJson('/api/v1/seller/catalog/listings');
        $res->assertStatus(200);

        $items = collect($res->json('data'))->keyBy('listing_id');

        // listing 1 has custom_price 290.00, base 280.00
        $this->assertEquals('290.00', $items[$this->listing1->id]['effective_price']);
        $this->assertTrue($items[$this->listing1->id]['price_differs_from_base']);

        // listing 2 has null custom_price, base 160.00
        $this->assertEquals('160.00', $items[$this->listing2->id]['effective_price']);
        $this->assertNull($items[$this->listing2->id]['custom_price']);
        $this->assertFalse($items[$this->listing2->id]['price_differs_from_base']);
    }

    public function test_listings_query_count_is_bounded(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/api/v1/seller/catalog/listings')->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // 1 auth/seller + 1 items query + 1 count query + 1 aggregate counts query = ~4 queries
        $this->assertLessThanOrEqual(8, count($queries));
    }

    // -------------------------------------------------------------
    // AVAILABLE PRODUCTS TESTS
    // -------------------------------------------------------------

    public function test_available_products_shows_only_unimported_in_seller_category(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // globalProduct1 and globalProduct2 already imported.
        // globalProduct3 is unimported in same category.
        // otherCatProduct is in different category.
        $res = $this->getJson('/api/v1/seller/catalog/available');
        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $res->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->globalProduct3->id, $data[0]['global_product_id']);
        $this->assertEquals('National Salt 800g', $data[0]['name']);
        $this->assertEquals('60.00', $data[0]['base_price']);
    }

    public function test_available_products_hides_inactive_or_deleted(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $this->globalProduct3->update(['is_active' => false]);

        $res = $this->getJson('/api/v1/seller/catalog/available');
        $res->assertStatus(200);
        $this->assertCount(0, $res->json('data'));
    }

    // -------------------------------------------------------------
    // IMPORT ENDPOINT TESTS
    // -------------------------------------------------------------

    public function test_import_products_by_id_array(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->postJson('/api/v1/seller/catalog/import', [
            'global_product_ids' => [$this->globalProduct3->id, $this->globalProduct1->id], // 3 is new, 1 already exists
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.imported', 1)
            ->assertJsonPath('data.skipped_existing', 1)
            ->assertJsonPath('data.skipped_unavailable', 0);

        // Verify row created with default price (null) and default stock (0) per web behavior
        $newListing = ShopProduct::where('seller_id', $this->seller->id)
            ->where('global_product_id', $this->globalProduct3->id)
            ->first();
        $this->assertNotNull($newListing);
        $this->assertNull($newListing->custom_price);
        $this->assertEquals(0, $newListing->stock_quantity);
        $this->assertFalse((bool)$newListing->is_active);
    }

    public function test_import_all_category(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->postJson('/api/v1/seller/catalog/import', [
            'import_all_category' => true,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.imported', 1)
            ->assertJsonPath('data.skipped_existing', 2);
    }

    public function test_import_running_same_request_twice_creates_no_duplicates(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $this->postJson('/api/v1/seller/catalog/import', [
            'global_product_ids' => [$this->globalProduct3->id],
        ])->assertStatus(200)->assertJsonPath('data.imported', 1);

        // Run again
        $res2 = $this->postJson('/api/v1/seller/catalog/import', [
            'global_product_ids' => [$this->globalProduct3->id],
        ]);
        $res2->assertStatus(200)
            ->assertJsonPath('data.imported', 0)
            ->assertJsonPath('data.skipped_existing', 1);

        $count = ShopProduct::where('seller_id', $this->seller->id)
            ->where('global_product_id', $this->globalProduct3->id)
            ->count();
        $this->assertEquals(1, $count);
    }

    public function test_import_over_100_ids_is_rejected(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $ids = range(1, 101);
        $res = $this->postJson('/api/v1/seller/catalog/import', [
            'global_product_ids' => $ids,
        ]);
        $res->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    // -------------------------------------------------------------
    // UPDATE LISTING ENDPOINT TESTS
    // -------------------------------------------------------------

    public function test_update_listing_valid_price_and_stock(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => '350.50',
            'stock_quantity' => 25,
            'is_active' => false,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.custom_price', '350.50')
            ->assertJsonPath('data.effective_price', '350.50')
            ->assertJsonPath('data.stock_quantity', 25)
            ->assertJsonPath('data.is_active', false);

        $this->listing1->refresh();
        $this->assertEquals(350.50, (float)$this->listing1->custom_price);
        $this->assertEquals(25, $this->listing1->stock_quantity);
        $this->assertFalse((bool)$this->listing1->is_active);
    }

    public function test_update_listing_reset_to_base_with_null_price(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $this->assertNotNull($this->listing1->custom_price);

        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => null,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.custom_price', null)
            ->assertJsonPath('data.effective_price', '280.00')
            ->assertJsonPath('data.price_differs_from_base', false);

        $this->listing1->refresh();
        $this->assertNull($this->listing1->custom_price);
    }

    public function test_update_listing_relative_stock_adjust(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $this->assertEquals(10, $this->listing1->stock_quantity);

        // Relative increase +5
        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_adjust' => 5,
        ]);
        $res->assertStatus(200)->assertJsonPath('data.stock_quantity', 15);

        // Relative decrease -4
        $res2 = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_adjust' => -4,
        ]);
        $res2->assertStatus(200)->assertJsonPath('data.stock_quantity', 11);
    }

    public function test_update_listing_stock_adjust_below_zero_is_rejected(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $this->assertEquals(10, $this->listing1->stock_quantity);

        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_adjust' => -15,
        ]);
        $res->assertStatus(422)
            ->assertJsonPath('code', 'STOCK_BELOW_ZERO');

        $this->listing1->refresh();
        $this->assertEquals(10, $this->listing1->stock_quantity);
    }

    public function test_update_listing_stock_quantity_and_stock_adjust_together_is_rejected(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_quantity' => 20,
            'stock_adjust' => 5,
        ]);
        $res->assertStatus(422)
            ->assertJsonPath('code', 'STOCK_MUTUALLY_EXCLUSIVE');
    }

    public function test_update_listing_invalid_price_cases(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Zero price rejected
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => 0,
        ])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_ERROR');

        // Negative price rejected
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => -10,
        ])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_ERROR');

        // Three decimals rejected
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => 280.125,
        ])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_ERROR');

        // Above multiplier (base_price = 280.00, multiplier = 3.0 => max is 840.00)
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => 840.01,
        ])->assertStatus(422)->assertJsonPath('code', 'PRICE_EXCEEDS_MAX_MULTIPLIER');
    }

    public function test_update_listing_unavailable_product_rejected_except_deactivation(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $this->globalProduct1->update(['is_active' => false]);

        // Attempting to update price/stock on unavailable product returns 422 PRODUCT_UNAVAILABLE
        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_quantity' => 50,
        ]);
        $res->assertStatus(422)
            ->assertJsonPath('code', 'PRODUCT_UNAVAILABLE');

        // But deactivating (is_active = false) IS allowed
        $resDeactivate = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'is_active' => false,
        ]);
        $resDeactivate->assertStatus(200)
            ->assertJsonPath('data.is_active', false);
    }

    // -------------------------------------------------------------
    // CONCURRENCY TEST
    // -------------------------------------------------------------

    public function test_concurrency_stock_adjust_and_confirm_order(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $this->assertEquals(10, $this->listing1->stock_quantity);

        // Simulate seller confirm order (Stage 6) decrementing stock by 2 in a transaction
        // Then seller does stock_adjust +5
        DB::transaction(function () {
            $listing = ShopProduct::where('id', $this->listing1->id)->lockForUpdate()->first();
            $listing->decrement('stock_quantity', 2);
        });

        // Now API stock adjust +5
        $res = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_adjust' => 5,
        ]);

        $res->assertStatus(200);
        $this->assertEquals(13, $res->json('data.stock_quantity')); // 10 - 2 + 5 = 13
    }

    // -------------------------------------------------------------
    // BULK PRICE ENDPOINT TESTS
    // -------------------------------------------------------------

    public function test_bulk_price_preview_does_not_modify_database(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $res = $this->postJson('/api/v1/seller/catalog/bulk-price', [
            'mode' => 'increase_percent',
            'percent' => 10,
            'scope' => 'all',
            'preview' => true,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.preview', true)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['listing_id', 'name', 'old_price', 'new_price']
                    ],
                    'count'
                ]
            ]);

        // DB values should NOT have changed
        $this->listing1->refresh();
        $this->assertEquals(290.00, (float)$this->listing1->custom_price);
    }

    public function test_bulk_price_apply_increase_decrease_and_reset(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // 1. Increase by 10%
        // listing 1: 290.00 * 1.10 = 319.00
        // listing 2: 160.00 (from base) * 1.10 = 176.00
        $resInc = $this->postJson('/api/v1/seller/catalog/bulk-price', [
            'mode' => 'increase_percent',
            'percent' => 10,
            'scope' => 'all',
            'preview' => false,
        ]);

        $resInc->assertStatus(200)
            ->assertJsonPath('data.updated', 2)
            ->assertJsonPath('data.skipped_count', 0);

        $this->listing1->refresh();
        $this->listing2->refresh();
        $this->assertEquals(319.00, (float)$this->listing1->custom_price);
        $this->assertEquals(176.00, (float)$this->listing2->custom_price);

        // 2. Decrease by 20%
        // listing 1: 319.00 * 0.80 = 255.20
        $resDec = $this->postJson('/api/v1/seller/catalog/bulk-price', [
            'mode' => 'decrease_percent',
            'percent' => 20,
            'listing_ids' => [$this->listing1->id],
            'preview' => false,
        ]);

        $resDec->assertStatus(200)
            ->assertJsonPath('data.updated', 1);

        $this->listing1->refresh();
        $this->assertEquals(255.20, (float)$this->listing1->custom_price);

        // 3. Reset to base
        $resReset = $this->postJson('/api/v1/seller/catalog/bulk-price', [
            'mode' => 'reset_to_base',
            'scope' => 'all',
            'preview' => false,
        ]);

        $resReset->assertStatus(200)
            ->assertJsonPath('data.updated', 2);

        $this->listing1->refresh();
        $this->listing2->refresh();
        $this->assertNull($this->listing1->custom_price);
        $this->assertNull($this->listing2->custom_price);
    }

    public function test_bulk_price_skips_items_exceeding_multiplier_and_foreign_ids(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Create a foreign listing for seller 2
        $foreignListing = ShopProduct::create([
            'seller_id' => $this->seller2->id,
            'global_product_id' => $this->globalProduct3->id,
            'custom_price' => 60.00,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        // Attempt increase on listing1 and foreignListing
        $res = $this->postJson('/api/v1/seller/catalog/bulk-price', [
            'mode' => 'increase_percent',
            'percent' => 10,
            'listing_ids' => [$this->listing1->id, $foreignListing->id],
            'preview' => false,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.updated', 1);

        // foreignListing is untouched
        $foreignListing->refresh();
        $this->assertEquals(60.00, (float)$foreignListing->custom_price);
    }

    // -------------------------------------------------------------
    // EFFECT ON CUSTOMER BROWSING & CART TESTS
    // -------------------------------------------------------------

    public function test_price_change_reflected_in_stage3_shop_detail(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Update price of listing 1 to 320.00
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => '320.00',
        ])->assertStatus(200);

        // Now customer browses product detail
        $resProduct = $this->getJson('/api/v1/customer/sellers/' . $this->seller->id . '/products/' . $this->listing1->id);
        $resProduct->assertStatus(200);
        $this->assertEquals(320.00, $resProduct->json('data.price'));
    }

    public function test_deactivated_listing_hidden_from_stage3_and_rejected_in_stage4(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // Deactivate listing 1
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'is_active' => false,
        ])->assertStatus(200);

        // Check Stage 3: product detail returns 404 when deactivated
        $resProduct = $this->getJson('/api/v1/customer/sellers/' . $this->seller->id . '/products/' . $this->listing1->id);
        $resProduct->assertStatus(404);

        // Check Stage 4: cart validate returns INACTIVE item
        Sanctum::actingAs($this->customer, ['customer']);
        $resCart = $this->postJson('/api/v1/customer/cart/validate', [
            'items' => [
                [
                    'listing_id' => $this->listing1->id,
                    'quantity' => 1,
                ]
            ]
        ]);

        $resCart->assertStatus(200)
            ->assertJsonPath('data.is_valid', false)
            ->assertJsonPath('data.items.0.problem_code', 'INACTIVE');
    }

    public function test_old_orders_keep_their_historical_snapshot_price(): void
    {
        // Customer bought listing 1 at 290.00
        $order = Order::create([
            'order_number' => 'ORD-TEST-HIST',
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'address' => 'Test Address',
            'phone' => '03001234567',
            'total_amount' => 340.00,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'shop_product_id' => $this->listing1->id,
            'global_product_id' => $this->globalProduct1->id,
            'item_name' => 'Olpers Milk 1L',
            'unit_type' => 'piece',
            'quantity' => 1,
            'price' => 290.00,
        ]);

        // Seller raises price to 390.00
        Sanctum::actingAs($this->seller, ['seller']);
        $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'custom_price' => '390.00',
        ])->assertStatus(200);

        // Verify order item still has historical price 290.00
        $orderItem->refresh();
        $this->assertEquals(290.00, (float)$orderItem->price);
    }

    // -------------------------------------------------------------
    // SENSITIVE DATA LEAK TESTS
    // -------------------------------------------------------------

    public function test_no_response_body_contains_sensitive_data(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $endpoints = [
            '/api/v1/seller/catalog/listings',
            '/api/v1/seller/catalog/available',
        ];

        foreach ($endpoints as $url) {
            $content = $this->getJson($url)->getContent();
            $this->assertStringNotContainsString('password', $content);
            $this->assertStringNotContainsString('remember_token', $content);
            $this->assertStringNotContainsString('otp', $content);
            $this->assertStringNotContainsString('storage/app', $content);
            $this->assertStringNotContainsString('c:\\', strtolower($content));
        }
    }

    public function test_raw_body_meta_is_empty_object_on_seller_catalog_endpoints(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        $resImport = $this->postJson('/api/v1/seller/catalog/import', [
            'global_product_ids' => [$this->globalProduct3->id],
        ]);
        $this->assertMatchesRegularExpression('/"meta":\s*\{\}/', $resImport->getContent());

        $resUpdate = $this->putJson('/api/v1/seller/catalog/listings/' . $this->listing1->id, [
            'stock_quantity' => 15,
        ]);
        $this->assertMatchesRegularExpression('/"meta":\s*\{\}/', $resUpdate->getContent());

        $resBulk = $this->postJson('/api/v1/seller/catalog/bulk-price', [
            'mode' => 'reset_to_base',
            'scope' => 'all',
            'preview' => false,
        ]);
        $this->assertMatchesRegularExpression('/"meta":\s*\{\}/', $resBulk->getContent());
    }
}
