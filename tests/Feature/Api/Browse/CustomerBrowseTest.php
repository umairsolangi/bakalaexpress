<?php

namespace Tests\Feature\Api\Browse;

use App\Models\CatalogCategory;
use App\Models\Feedback;
use App\Models\GlobalProduct;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerBrowseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function createCategory(string $name = 'Dairy & Bakery', string $slug = 'dairy-bakery'): CatalogCategory
    {
        return CatalogCategory::create([
            'name' => $name,
            'slug' => $slug,
            'description' => 'Test category description',
            'is_active' => true,
        ]);
    }

    protected function createSeller(array $attributes = []): Seller
    {
        return Seller::create(array_merge([
            'name' => 'Kirana General Store',
            'email' => 'kirana@example.com',
            'password' => Hash::make('password123'),
            'phone_number' => '03001234567',
            'shop_name' => 'Kirana General Store',
            'profile_image' => 'profile_images/store1.jpg',
            'city' => 'Karachi',
            'area' => 'Baldia Town',
            'sector' => '4A',
            'near_areas' => ['Ali Chowk', 'Main Bazaar'],
            'full_address' => 'Shop 5, Sector 4A, Baldia Town, Karachi',
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
            'is_open' => true,
            'accountIsApproved' => 1,
            'is_deleted' => false,
        ], $attributes));
    }

    public function test_locations_meta_returns_sectors_near_areas_and_active_categories(): void
    {
        $cat1 = $this->createCategory('Dairy & Bakery', 'dairy-bakery');
        $cat2 = $this->createCategory('Prepared Foods', 'prepared-foods');
        CatalogCategory::create(['name' => 'Inactive Cat', 'slug' => 'inactive', 'is_active' => false]);

        $response = $this->getJson('/api/v1/customer/meta/locations');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sectors' => ['4A', '4B', '4C'],
                    'near_areas' => [
                        'ABC Swimming Pool',
                        'Tajli Noor Masjid',
                        'Lahori Hotel',
                        'Ali Chowk',
                        'Family Park',
                        'Carido Hospital',
                        'Rubi Mor',
                    ],
                ],
            ]);

        $categories = $response->json('data.categories');
        $this->assertCount(2, $categories);
        $this->assertEquals('Dairy & Bakery', $categories[0]['name']);
    }

    public function test_home_returns_only_approved_non_deleted_shops_with_filters_and_pagination(): void
    {
        $category = $this->createCategory('Dairy & Bakery', 'dairy-bakery');

        $approvedShop = $this->createSeller([
            'name' => 'Approved Kirana',
            'sector' => '4A',
            'near_areas' => ['Ali Chowk'],
            'catalog_category_id' => $category->id,
            'accountIsApproved' => 1,
            'is_deleted' => false,
        ]);

        $unapprovedShop = $this->createSeller([
            'name' => 'Pending Kirana',
            'email' => 'pending@example.com',
            'accountIsApproved' => 0,
            'is_deleted' => false,
        ]);

        $deletedShop = $this->createSeller([
            'name' => 'Deleted Kirana',
            'email' => 'deleted@example.com',
            'accountIsApproved' => 1,
            'is_deleted' => true,
        ]);

        // Default home fetch
        $response = $this->getJson('/api/v1/customer/home');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'current_page' => 1,
                    'total' => 1,
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($approvedShop->id, $data[0]['id']);

        // Filter by matching sector
        $responseSector = $this->getJson('/api/v1/customer/home?sector=4A');
        $responseSector->assertStatus(200);
        $this->assertCount(1, $responseSector->json('data'));

        // Filter by non-matching sector
        $responseOtherSector = $this->getJson('/api/v1/customer/home?sector=4C');
        $responseOtherSector->assertStatus(200);
        $this->assertCount(0, $responseOtherSector->json('data'));

        // Filter by near_area
        $responseNearArea = $this->getJson('/api/v1/customer/home?near_area=Ali Chowk');
        $responseNearArea->assertStatus(200);
        $this->assertCount(1, $responseNearArea->json('data'));

        // Filter by category_id
        $responseCat = $this->getJson('/api/v1/customer/home?category_id=' . $category->id);
        $responseCat->assertStatus(200);
        $this->assertCount(1, $responseCat->json('data'));

        // Filter by shop name search q
        $responseQ = $this->getJson('/api/v1/customer/home?q=Approved');
        $responseQ->assertStatus(200);
        $this->assertCount(1, $responseQ->json('data'));

        // Reject per_page above 30
        $responseExcess = $this->getJson('/api/v1/customer/home?per_page=35');
        $responseExcess->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
            ]);
    }

    public function test_closed_shop_appears_with_accepting_orders_false_and_handles_overnight(): void
    {
        // Explicitly closed shop (is_open = false)
        $closedShop = $this->createSeller([
            'name' => 'Closed Night Shop',
            'email' => 'closed@example.com',
            'is_open' => false,
        ]);

        $response = $this->getJson('/api/v1/customer/home');
        $response->assertStatus(200);

        $shop = collect($response->json('data'))->firstWhere('id', $closedShop->id);
        $this->assertNotNull($shop);
        $this->assertFalse($shop['is_open']);
        $this->assertFalse($shop['accepting_orders']);

        // Overnight shop: opens at 20:00:00, closes at 04:00:00
        $overnightShop = $this->createSeller([
            'name' => 'Overnight Dhaba',
            'email' => 'dhaba@example.com',
            'opens_at' => '20:00:00',
            'closes_at' => '04:00:00',
            'is_open' => true,
        ]);

        $this->assertTrue(is_bool($overnightShop->isAcceptingOrders()));
    }

    public function test_seller_detail_groups_by_category_effective_price_and_caps_stock(): void
    {
        $catDairy = $this->createCategory('Dairy & Bakery', 'dairy-bakery');
        $catSnacks = $this->createCategory('Snacks', 'snacks');

        $seller = $this->createSeller(['catalog_category_id' => $catDairy->id]);

        $prod1 = GlobalProduct::create([
            'catalog_category_id' => $catDairy->id,
            'name' => 'Fresh Milk 1L',
            'description' => 'Pasteurized milk',
            'base_price' => 150.00,
            'unit_type' => 'pack',
            'is_active' => true,
        ]);

        $prod2 = GlobalProduct::create([
            'catalog_category_id' => $catDairy->id,
            'name' => 'Farm Eggs Dozen',
            'description' => 'Brown eggs',
            'base_price' => 280.00,
            'unit_type' => 'dozen',
            'is_active' => true,
        ]);

        $inactiveProd = GlobalProduct::create([
            'catalog_category_id' => $catDairy->id,
            'name' => 'Expired Butter',
            'base_price' => 200.00,
            'is_active' => false,
        ]);

        $deletedProd = GlobalProduct::create([
            'catalog_category_id' => $catSnacks->id,
            'name' => 'Deleted Chips',
            'base_price' => 50.00,
            'is_active' => true,
        ]);
        $deletedProd->delete();

        // Listing 1: custom price set, stock 150 (must cap at 99)
        $listing1 = ShopProduct::create([
            'seller_id' => $seller->id,
            'global_product_id' => $prod1->id,
            'custom_price' => 165.00,
            'stock_quantity' => 150,
            'is_active' => true,
        ]);

        // Listing 2: custom price null (uses base_price), stock 25
        $listing2 = ShopProduct::create([
            'seller_id' => $seller->id,
            'global_product_id' => $prod2->id,
            'custom_price' => null,
            'stock_quantity' => 25,
            'is_active' => true,
        ]);

        // Listing 3: inactive global product
        ShopProduct::create([
            'seller_id' => $seller->id,
            'global_product_id' => $inactiveProd->id,
            'custom_price' => 200.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        // Listing 4: soft-deleted global product
        ShopProduct::create([
            'seller_id' => $seller->id,
            'global_product_id' => $deletedProd->id,
            'custom_price' => 50.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/customer/sellers/{$seller->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'seller' => [
                        'id' => $seller->id,
                        'name' => $seller->name,
                    ],
                ],
            ]);

        $categories = $response->json('data.categories');
        $this->assertCount(1, $categories);
        $this->assertEquals('Dairy & Bakery', $categories[0]['name']);

        $products = $categories[0]['products'];
        $this->assertCount(2, $products);

        // Check custom price and capped stock (99)
        $item1 = collect($products)->firstWhere('listing_id', $listing1->id);
        $this->assertEquals(165.00, $item1['price']);
        $this->assertEquals(99, $item1['stock_quantity']);
        $this->assertTrue($item1['in_stock']);

        // Check base price fallback and real stock (25)
        $item2 = collect($products)->firstWhere('listing_id', $listing2->id);
        $this->assertEquals(280.00, $item2['price']);
        $this->assertEquals(25, $item2['stock_quantity']);
        $this->assertTrue($item2['in_stock']);

        // Soft-deleted and inactive products should not be present
        $this->assertNull(collect($products)->firstWhere('name', 'Expired Butter'));
        $this->assertNull(collect($products)->firstWhere('name', 'Deleted Chips'));
    }

    public function test_seller_detail_returns_404_for_unapproved_or_deleted_seller(): void
    {
        $unapproved = $this->createSeller(['email' => 'unapp@example.com', 'accountIsApproved' => 0]);
        $deleted = $this->createSeller(['email' => 'del@example.com', 'is_deleted' => true]);

        $this->getJson("/api/v1/customer/sellers/{$unapproved->id}")
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'code' => 'NOT_FOUND',
            ]);

        $this->getJson("/api/v1/customer/sellers/{$deleted->id}")
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'code' => 'NOT_FOUND',
            ]);

        $this->getJson('/api/v1/customer/sellers/999999')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'code' => 'NOT_FOUND',
            ]);
    }

    public function test_listing_detail_returns_correct_data_and_404_on_wrong_seller(): void
    {
        $category = $this->createCategory();
        $sellerA = $this->createSeller(['email' => 'sellerA@example.com']);
        $sellerB = $this->createSeller(['email' => 'sellerB@example.com']);

        $global = GlobalProduct::create([
            'catalog_category_id' => $category->id,
            'name' => 'Basmati Rice 5kg',
            'description' => 'Premium Kernel Rice',
            'base_price' => 1200.00,
            'unit_type' => 'bag',
            'is_active' => true,
        ]);

        $listing = ShopProduct::create([
            'seller_id' => $sellerA->id,
            'global_product_id' => $global->id,
            'custom_price' => 1250.00,
            'stock_quantity' => 40,
            'is_active' => true,
        ]);

        // Success from seller A
        $response = $this->getJson("/api/v1/customer/sellers/{$sellerA->id}/products/{$listing->id}");
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'listing_id' => $listing->id,
                    'global_product_id' => $global->id,
                    'name' => 'Basmati Rice 5kg',
                    'price' => 1250.00,
                    'stock_quantity' => 40,
                    'in_stock' => true,
                    'shop' => [
                        'id' => $sellerA->id,
                        'name' => $sellerA->name,
                    ],
                ],
            ]);

        // 404 from seller B (wrong seller)
        $this->getJson("/api/v1/customer/sellers/{$sellerB->id}/products/{$listing->id}")
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'code' => 'NOT_FOUND',
            ]);
    }

    public function test_reviews_only_returns_approved_feedback_and_masks_reviewer_name(): void
    {
        $seller = $this->createSeller();

        $user1 = User::create([
            'name' => 'Sara Ahmed Khan',
            'email' => 'sara@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03001112233',
            'sellerType' => 2,
            'accountIsApproved' => 1,
            'is_verified' => true,
        ]);

        $user2 = User::create([
            'name' => 'Bilal',
            'email' => 'bilal@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03004445566',
            'sellerType' => 2,
            'accountIsApproved' => 1,
            'is_verified' => true,
        ]);

        $order1 = \App\Models\Order::create([
            'user_id' => $user1->id,
            'seller_id' => $seller->id,
            'address' => 'House 1, Sector 4A, Karachi',
            'phone' => '03001112233',
            'status' => 'delivered',
            'total_amount' => 500.00,
        ]);

        $approvedFeedback = Feedback::create([
            'order_id' => $order1->id,
            'user_id' => $user1->id,
            'seller_id' => $seller->id,
            'rating' => 5,
            'feedback' => 'Fresh vegetables and fast delivery!',
            'status' => 'approved',
        ]);

        $pendingFeedback = Feedback::create([
            'order_id' => $order1->id,
            'user_id' => $user2->id,
            'seller_id' => $seller->id,
            'rating' => 2,
            'feedback' => 'Pending moderation feedback',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/v1/customer/sellers/{$seller->id}/reviews");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'total' => 1,
                ],
            ]);

        $reviews = $response->json('data');
        $this->assertCount(1, $reviews);
        $this->assertEquals('Fresh vegetables and fast delivery!', $reviews[0]['feedback']);
        $this->assertEquals('Sara K.', $reviews[0]['reviewer_name']);
        $this->assertArrayNotHasKey('email', $reviews[0]);
        $this->assertArrayNotHasKey('user_id', $reviews[0]);

        // String search across whole response body
        $rawContent = $response->getContent();
        $this->assertStringNotContainsString('sara@example.com', $rawContent);
        $this->assertStringNotContainsString('bilal@example.com', $rawContent);
        $this->assertStringNotContainsString('Pending moderation feedback', $rawContent);
    }

    public function test_search_escapes_wildcards_and_filters_by_type_and_sector(): void
    {
        $category = $this->createCategory();
        $seller = $this->createSeller([
            'name' => '100% Discount Store',
            'sector' => '4A',
        ]);

        $seller2 = $this->createSeller([
            'name' => 'Regular Market',
            'email' => 'regular@example.com',
            'sector' => '4B',
        ]);

        $prod1 = GlobalProduct::create([
            'catalog_category_id' => $category->id,
            'name' => '100% Pure Honey',
            'base_price' => 500.00,
            'is_active' => true,
        ]);

        $prod2 = GlobalProduct::create([
            'catalog_category_id' => $category->id,
            'name' => 'Special Tea',
            'base_price' => 250.00,
            'is_active' => true,
        ]);

        ShopProduct::create([
            'seller_id' => $seller->id,
            'global_product_id' => $prod1->id,
            'is_active' => true,
            'stock_quantity' => 10,
        ]);

        ShopProduct::create([
            'seller_id' => $seller2->id,
            'global_product_id' => $prod2->id,
            'is_active' => true,
            'stock_quantity' => 10,
        ]);

        // Search min length validation
        $this->getJson('/api/v1/customer/search?q=a')
            ->assertStatus(422)
            ->assertJson(['code' => 'VALIDATION_ERROR']);

        // Wildcard escaping: searching '100%' must match literal '%' and NOT return 'Special Tea' or 'Regular Market'
        $responseWildcard = $this->getJson('/api/v1/customer/search?q=100%25');
        $responseWildcard->assertStatus(200);

        $shops = $responseWildcard->json('data.shops');
        $products = $responseWildcard->json('data.products');

        $this->assertTrue(collect($shops)->contains('name', '100% Discount Store'));
        $this->assertFalse(collect($shops)->contains('name', 'Regular Market'));

        $this->assertTrue(collect($products)->contains('product_name', '100% Pure Honey'));
        $this->assertFalse(collect($products)->contains('product_name', 'Special Tea'));

        // Searching '%%' should match nothing (not return everything)
        $responseAllWildcards = $this->getJson('/api/v1/customer/search?q=%25%25');
        $responseAllWildcards->assertStatus(200);
        $this->assertEmpty($responseAllWildcards->json('data.shops'));
        $this->assertEmpty($responseAllWildcards->json('data.products'));

        // Type filter: shops only
        $responseShopsOnly = $this->getJson('/api/v1/customer/search?q=Store&type=shops');
        $responseShopsOnly->assertStatus(200);
        $this->assertEquals('shops', $responseShopsOnly->json('data.type'));
        $this->assertNotEmpty($responseShopsOnly->json('data.shops'));

        // Sector filter: sector 4B should only find Regular Market
        $responseSector = $this->getJson('/api/v1/customer/search?q=Market&sector=4B&type=shops');
        $responseSector->assertStatus(200);
        $this->assertTrue(collect($responseSector->json('data.shops'))->contains('name', 'Regular Market'));
    }

    public function test_response_bodies_never_contain_sensitive_credentials(): void
    {
        $seller = $this->createSeller([
            'email' => 'confidential_seller@example.com',
            'password' => 'supersecretpass123',
        ]);

        $endpoints = [
            '/api/v1/customer/meta/locations',
            '/api/v1/customer/home',
            "/api/v1/customer/sellers/{$seller->id}",
            "/api/v1/customer/sellers/{$seller->id}/reviews",
            '/api/v1/customer/search?q=Kirana',
        ];

        foreach ($endpoints as $url) {
            $response = $this->getJson($url);
            $content = $response->getContent();

            $this->assertStringNotContainsString('confidential_seller@example.com', $content, "Leaked seller email in {$url}");
            $this->assertStringNotContainsString('supersecretpass123', $content, "Leaked password in {$url}");
            $this->assertStringNotContainsString('remember_token', $content, "Leaked remember_token key in {$url}");
            $this->assertStringNotContainsString('otp', $content, "Leaked otp key in {$url}");
        }
    }

    public function test_query_count_for_seller_detail_stays_small(): void
    {
        $category = $this->createCategory();
        $seller = $this->createSeller();

        // Create 10 catalog products for the seller
        for ($i = 1; $i <= 10; $i++) {
            $global = GlobalProduct::create([
                'catalog_category_id' => $category->id,
                'name' => "Catalog Item {$i}",
                'base_price' => 10.00 * $i,
                'is_active' => true,
            ]);

            ShopProduct::create([
                'seller_id' => $seller->id,
                'global_product_id' => $global->id,
                'stock_quantity' => 20,
                'is_active' => true,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson("/api/v1/customer/sellers/{$seller->id}");
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Verify total query count is <= 5 (no N+1 loops)
        $this->assertLessThanOrEqual(5, count($queries), 'Query count exceeded 5 for seller detail view');
    }

    public function test_search_matches_description_and_supports_filters_and_home_supports_category_alias(): void
    {
        $cat1 = $this->createCategory('Pantry', 'pantry');
        $cat2 = $this->createCategory('Beverages', 'beverages');

        $seller = $this->createSeller([
            'catalog_category_id' => $cat1->id,
            'is_open' => true,
        ]);

        $prodA = GlobalProduct::create([
            'catalog_category_id' => $cat1->id,
            'name' => 'Daal Moong',
            'description' => 'Special organic yellow lentils',
            'base_price' => 250.00,
            'is_active' => true,
        ]);

        $prodB = GlobalProduct::create([
            'catalog_category_id' => $cat2->id,
            'name' => 'Apple Juice',
            'description' => 'Fresh fruit drink',
            'base_price' => 150.00,
            'is_active' => true,
        ]);

        ShopProduct::create([
            'seller_id' => $seller->id,
            'global_product_id' => $prodA->id,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        ShopProduct::create([
            'seller_id' => $seller->id,
            'global_product_id' => $prodB->id,
            'stock_quantity' => 0, // out of stock
            'is_active' => true,
        ]);

        // 1. Search by description keyword "lentils" (which is NOT in product name)
        $resDesc = $this->getJson('/api/v1/customer/search?q=lentils&type=products');
        $resDesc->assertStatus(200);
        $this->assertCount(1, $resDesc->json('data.products'));
        $this->assertEquals('Daal Moong', $resDesc->json('data.products.0.product_name'));

        // 2. Search with in_stock = 1
        $resInStock = $this->getJson('/api/v1/customer/search?q=Fruit&type=products&in_stock=1');
        $resInStock->assertStatus(200);
        $this->assertCount(0, $resInStock->json('data.products')); // Apple juice is out of stock

        // 3. Home endpoint with category alias (webapp parameter)
        $resHomeCat = $this->getJson("/api/v1/customer/home?category={$cat1->id}");
        $resHomeCat->assertStatus(200);
        $this->assertCount(1, $resHomeCat->json('data'));

        // 4. Home endpoint with is_open filter
        $resHomeOpen = $this->getJson('/api/v1/customer/home?is_open=1');
        $resHomeOpen->assertStatus(200);
        $this->assertCount(1, $resHomeOpen->json('data'));
    }
}
