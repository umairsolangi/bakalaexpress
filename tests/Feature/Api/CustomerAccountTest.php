<?php

namespace Tests\Feature\Api;

use App\Mail\AccountDeletionRequestMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\CatalogCategory;
use App\Models\DeviceToken;
use App\Models\Favorite;
use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Models\UserProfileUpdate;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use App\Support\Api\FavoriteLookup;
use App\Support\Api\LocationOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $customer2;
    protected Seller $seller;
    protected Rider $rider;
    protected CatalogCategory $category;
    protected GlobalProduct $globalProduct1;
    protected GlobalProduct $globalProduct2;
    protected ShopProduct $shopProduct1;
    protected ShopProduct $shopProduct2;

    protected function setUp(): void
    {
        parent::setUp();

        FavoriteLookup::reset();
        Storage::fake('public');
        Mail::fake();

        $this->category = CatalogCategory::create([
            'name' => 'Groceries',
            'slug' => 'groceries',
            'is_active' => true,
        ]);

        $this->seller = Seller::create([
            'name' => 'Al-Madina Mart',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'city' => 'Karachi',
            'area' => 'Gulshan',
            'sector' => '4A',
            'near_areas' => ['Ali Chowk'],
            'full_address' => 'Shop 5, Sector 4A, Gulshan, Karachi',
            'opens_at' => '08:00:00',
            'closes_at' => '23:00:00',
            'is_open' => true,
            'catalog_category_id' => $this->category->id,
            'accountIsApproved' => 1,
            'is_deleted' => 0,
        ]);

        $this->customer = User::create([
            'name' => 'Fatima Customer',
            'email' => 'fatima@example.com',
            'password' => Hash::make('password123'),
            'phone' => '03009998877',
            'city' => 'Karachi',
            'sellerType' => 0,
            'is_verified' => true,
        ]);
        $this->customer->forceFill([
            'sector' => '4A',
            'near_area' => 'Ali Chowk',
        ])->save();

        $this->customer2 = User::create([
            'name' => 'Usman Customer',
            'email' => 'usman@example.com',
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

        $this->globalProduct1 = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'Brown Sugar 1kg',
            'slug' => 'brown-sugar-1kg',
            'base_price' => 180.00,
            'unit_type' => 'packet',
            'is_active' => true,
        ]);

        $this->globalProduct2 = GlobalProduct::create([
            'catalog_category_id' => $this->category->id,
            'name' => 'Basmati Rice 5kg',
            'slug' => 'basmati-rice-5kg',
            'base_price' => 1200.00,
            'unit_type' => 'bag',
            'is_active' => true,
        ]);

        $this->shopProduct1 = ShopProduct::create([
            'seller_id' => $this->seller->id,
            'global_product_id' => $this->globalProduct1->id,
            'custom_price' => 185.00,
            'stock_quantity' => 15,
            'is_active' => true,
        ]);

        $this->shopProduct2 = ShopProduct::create([
            'seller_id' => $this->seller->id,
            'global_product_id' => $this->globalProduct2->id,
            'custom_price' => 1250.00,
            'stock_quantity' => 8,
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create a valid genuine GD test image file.
     */
    protected function createValidGdImage(string $name = 'avatar.jpg'): UploadedFile
    {
        $im = imagecreatetruecolor(100, 100);
        $bg = imagecolorallocate($im, 200, 50, 50);
        imagefill($im, 0, 0, $bg);
        $tempPath = tempnam(sys_get_temp_dir(), 'test_img_') . '.jpg';
        imagejpeg($im, $tempPath, 90);
        imagedestroy($im);

        return new UploadedFile(
            $tempPath,
            $name,
            'image/jpeg',
            null,
            true
        );
    }

    // -------------------------------------------------------------
    // 1. PROFILE TESTS
    // -------------------------------------------------------------

    public function test_get_customer_profile(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $res = $this->getJson('/api/v1/customer/profile');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->customer->id)
            ->assertJsonPath('data.name', 'Fatima Customer')
            ->assertJsonPath('data.email', 'fatima@example.com')
            ->assertJsonPath('data.sector', '4A')
            ->assertJsonPath('data.near_area', 'Ali Chowk')
            ->assertJsonPath('data.avatar_url', null);
    }

    public function test_update_customer_profile_and_avatar(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        $imageFile = $this->createValidGdImage('my_profile.jpg');

        $res = $this->postJson('/api/v1/customer/profile', [
            '_method' => 'PUT',
            'name' => 'Fatima Updated',
            'sector' => '4B',
            'near_area' => 'Family Park',
            'profile_image' => $imageFile,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Fatima Updated')
            ->assertJsonPath('data.sector', '4B')
            ->assertJsonPath('data.near_area', 'Family Park');

        $this->assertNotNull($res->json('data.avatar_url'));
        $this->assertStringContainsString('profile_images/', $res->json('data.avatar_url'));

        // Verify database and storage
        $profileUpdate = UserProfileUpdate::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($profileUpdate);
        Storage::disk('public')->assertExists($profileUpdate->profile_image);

        // Verify email cannot be changed
        $this->assertEquals('fatima@example.com', $this->customer->fresh()->email);
    }

    public function test_customer_change_password_and_token_revocation(): void
    {
        $token1Obj = $this->customer->createToken('token1', ['customer']);
        $token2Obj = $this->customer->createToken('token2', ['customer']);

        // Wrong current password
        $resWrong = $this->withToken($token2Obj->plainTextToken)
            ->postJson('/api/v1/customer/profile/password', [
                'current_password' => 'wrongpass',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);
        $resWrong->assertStatus(422)->assertJsonPath('code', 'INVALID_PASSWORD');

        // Correct password
        $resOk = $this->withToken($token2Obj->plainTextToken)
            ->postJson('/api/v1/customer/profile/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);
        $resOk->assertStatus(200)->assertJsonPath('success', true);

        // New password matches
        $this->assertTrue(Hash::check('newpassword123', $this->customer->fresh()->password));

        // Token 1 was revoked, Token 2 (current) was kept
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token1Obj->accessToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token2Obj->accessToken->id]);
    }

    // -------------------------------------------------------------
    // 2. FAVORITES TESTS
    // -------------------------------------------------------------

    public function test_favorite_toggles_and_lists(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        // 1. Toggle Seller ON
        $toggleSellerRes = $this->postJson("/api/v1/customer/favorites/sellers/{$this->seller->id}/toggle");
        $toggleSellerRes->assertStatus(200)->assertJsonPath('data.is_favorite', true);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
        ]);

        // 2. Toggle Listing ON
        $toggleProductRes = $this->postJson("/api/v1/customer/favorites/products/{$this->shopProduct1->id}/toggle");
        $toggleProductRes->assertStatus(200)->assertJsonPath('data.is_favorite', true);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->customer->id,
            'shop_product_id' => $this->shopProduct1->id,
        ]);

        // 3. List favorite sellers
        $listSellersRes = $this->getJson('/api/v1/customer/favorites/sellers');
        $listSellersRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->seller->id)
            ->assertJsonPath('data.0.is_favorite', true);

        // 4. List favorite products
        $listProductsRes = $this->getJson('/api/v1/customer/favorites/products');
        $listProductsRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.listing_id', $this->shopProduct1->id)
            ->assertJsonPath('data.0.is_favorite', true);

        // 5. Toggle Seller OFF
        $toggleSellerOff = $this->postJson("/api/v1/customer/favorites/sellers/{$this->seller->id}/toggle");
        $toggleSellerOff->assertStatus(200)->assertJsonPath('data.is_favorite', false);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
        ]);
    }

    public function test_is_favorite_on_stage3_public_endpoints_with_and_without_token(): void
    {
        Favorite::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'shop_product_id' => $this->shopProduct1->id,
        ]);

        // 1. Without token: public access works and is_favorite is false
        FavoriteLookup::reset();
        $publicHomeRes = $this->getJson('/api/v1/customer/home');
        $publicHomeRes->assertStatus(200)
            ->assertJsonPath('data.0.is_favorite', false);

        $publicDetailRes = $this->getJson("/api/v1/customer/sellers/{$this->seller->id}");
        $publicDetailRes->assertStatus(200)
            ->assertJsonPath('data.seller.is_favorite', false)
            ->assertJsonPath('data.categories.0.products.0.is_favorite', false);

        // 2. With customer token: is_favorite reflects true
        FavoriteLookup::reset();
        Sanctum::actingAs($this->customer, ['customer']);

        $authHomeRes = $this->getJson('/api/v1/customer/home');
        $authHomeRes->assertStatus(200)
            ->assertJsonPath('data.0.is_favorite', true);

        FavoriteLookup::reset();
        $authDetailRes = $this->getJson("/api/v1/customer/sellers/{$this->seller->id}");
        $authDetailRes->assertStatus(200)
            ->assertJsonPath('data.seller.is_favorite', true)
            ->assertJsonPath('data.categories.0.products.0.is_favorite', true);
    }

    public function test_favorites_query_count_is_bounded(): void
    {
        Favorite::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
        ]);

        Sanctum::actingAs($this->customer, ['customer']);
        FavoriteLookup::reset();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/api/v1/customer/home')->assertStatus(200);

        $queries = DB::getQueryLog();
        // Check favorite queries: should be at most 1 query on favorites table
        $favoriteQueries = array_filter($queries, fn($q) => str_contains(strtolower($q['query']), 'favorites'));
        $this->assertLessThanOrEqual(1, count($favoriteQueries));
    }

    // -------------------------------------------------------------
    // 3. NOTIFICATIONS TESTS
    // -------------------------------------------------------------

    public function test_notifications_list_shape_and_mark_as_read(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'Gulshan',
            'phone' => '03001234567',
            'status' => 'confirmed_by_seller',
            'total_amount' => 500.00,
        ]);

        $this->customer->notify(new OrderStatusNotification($order, 'confirmed_by_seller'));
        $this->seller->notify(new NewOrderNotification($this->seller->name, $order->id, json_encode([['service_id' => 1, 'quantity' => 1, 'price' => 500]])));

        // Customer notifications
        Sanctum::actingAs($this->customer, ['customer']);
        $custNotifRes = $this->getJson('/api/v1/customer/notifications');
        $custNotifRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonPath('data.0.type', 'order_status')
            ->assertJsonPath('data.0.order_id', (string) $order->id)
            ->assertJsonPath('data.0.status', 'confirmed_by_seller')
            ->assertJsonPath('data.0.read_at', null);

        $notifId = $custNotifRes->json('data.0.id');

        // Mark customer notification read
        $markRes = $this->postJson('/api/v1/customer/notifications/read', [
            'ids' => [$notifId],
        ]);
        $markRes->assertStatus(200)->assertJsonPath('data.unread_count', 0);

        // Seller notifications
        Sanctum::actingAs($this->seller, ['seller']);
        $sellerNotifRes = $this->getJson('/api/v1/seller/notifications');
        $sellerNotifRes->assertStatus(200)
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonPath('data.0.type', 'new_order')
            ->assertJsonPath('data.0.order_id', (string) $order->id);

        // Mark all seller notifications read
        $this->postJson('/api/v1/seller/notifications/read', ['all' => true])
            ->assertStatus(200)
            ->assertJsonPath('data.unread_count', 0);
    }

    public function test_customer_cannot_mark_another_users_notifications(): void
    {
        $order = Order::create([
            'user_id' => $this->customer2->id,
            'seller_id' => $this->seller->id,
            'address' => 'Clifton',
            'phone' => '03001234567',
            'status' => 'confirmed_by_seller',
            'total_amount' => 500.00,
        ]);
        $this->customer2->notify(new OrderStatusNotification($order, 'confirmed_by_seller'));

        $otherNotif = $this->customer2->unreadNotifications()->first();

        Sanctum::actingAs($this->customer, ['customer']);
        $this->postJson('/api/v1/customer/notifications/read', [
            'ids' => [$otherNotif->id],
        ]);

        // Customer2's notification is still unread
        $this->assertNull($otherNotif->fresh()->read_at);
    }

    // -------------------------------------------------------------
    // 4. DELIVERY SECTOR & RIDER HINT TESTS
    // -------------------------------------------------------------

    public function test_order_placement_saves_sector_from_request_or_profile_fallback(): void
    {
        Sanctum::actingAs($this->customer, ['customer']);

        // 1. Explicit sector in order request
        $res1 = $this->withHeaders([
            'Idempotency-Key' => 'sector-order-key-1111111111111111',
        ])->postJson('/api/v1/customer/orders', [
            'seller_id' => $this->seller->id,
            'address' => 'House 1, Street 2',
            'phone' => '03009998877',
            'payment_method' => 'cod',
            'delivery_sector' => '4B',
            'delivery_near_area' => 'Tajli Noor Masjid',
            'items' => [
                ['listing_id' => $this->shopProduct1->id, 'quantity' => 1]
            ],
        ]);
        $res1->assertStatus(201);
        $order1Id = $res1->json('data.id');
        $this->assertDatabaseHas('orders', [
            'id' => $order1Id,
            'delivery_sector' => '4B',
            'delivery_near_area' => 'Tajli Noor Masjid',
        ]);

        // 2. Missing in request: fall back to customer profile (4A, Ali Chowk)
        $res2 = $this->withHeaders([
            'Idempotency-Key' => 'sector-order-key-2222222222222222',
        ])->postJson('/api/v1/customer/orders', [
            'seller_id' => $this->seller->id,
            'address' => 'House 10, Street 5',
            'phone' => '03009998877',
            'payment_method' => 'cod',
            'items' => [
                ['listing_id' => $this->shopProduct1->id, 'quantity' => 1]
            ],
        ]);
        $res2->assertStatus(201);
        $order2Id = $res2->json('data.id');
        $this->assertDatabaseHas('orders', [
            'id' => $order2Id,
            'delivery_sector' => '4A',
            'delivery_near_area' => 'Ali Chowk',
        ]);

        // 3. Invalid sector rejected
        $res3 = $this->withHeaders([
            'Idempotency-Key' => 'sector-order-key-3333333333333333',
        ])->postJson('/api/v1/customer/orders', [
            'seller_id' => $this->seller->id,
            'address' => 'House 10',
            'phone' => '03009998877',
            'payment_method' => 'cod',
            'delivery_sector' => 'INVALID_SECTOR',
            'items' => [
                ['listing_id' => $this->shopProduct1->id, 'quantity' => 1]
            ],
        ]);
        $res3->assertStatus(422);

        // 4. Rider customer_area_hint uses delivery_near_area and delivery_sector, never street text
        $order = Order::find($order1Id);
        $order->update(['status' => 'ready_for_pickup']);

        Sanctum::actingAs($this->rider, ['rider']);
        $availRes = $this->getJson('/api/v1/rider/orders/available');
        $availRes->assertStatus(200);

        $availOrder = collect($availRes->json('data'))->firstWhere('id', $order1Id);
        $this->assertNotNull($availOrder);
        $this->assertEquals('Tajli Noor Masjid, Sector 4B', $availOrder['customer_area_hint']);
        $this->assertStringNotContainsString('House 1', json_encode($availOrder));
    }

    // -------------------------------------------------------------
    // 5. REORDER TESTS
    // -------------------------------------------------------------

    public function test_reorder_preview_availability_and_ownership(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'Gulshan',
            'phone' => '03009998877',
            'status' => 'delivered',
            'total_amount' => 1435.00,
        ]);

        // Item 1: in stock
        OrderItem::create([
            'order_id' => $order->id,
            'shop_product_id' => $this->shopProduct1->id,
            'item_name' => 'Brown Sugar 1kg',
            'quantity' => 2,
            'price' => 185.00,
        ]);

        // Item 2: set stock to 0
        $this->shopProduct2->update(['stock_quantity' => 0]);
        OrderItem::create([
            'order_id' => $order->id,
            'shop_product_id' => $this->shopProduct2->id,
            'item_name' => 'Basmati Rice 5kg',
            'quantity' => 1,
            'price' => 1250.00,
        ]);

        Sanctum::actingAs($this->customer, ['customer']);
        $reorderRes = $this->getJson("/api/v1/customer/orders/{$order->id}/reorder");

        $reorderRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.ok', true)
            ->assertJsonPath('data.items.0.problem_code', null)
            ->assertJsonPath('data.items.1.ok', false)
            ->assertJsonPath('data.items.1.problem_code', 'OUT_OF_STOCK');

        // Other customer gets 404
        Sanctum::actingAs($this->customer2, ['customer']);
        $this->getJson("/api/v1/customer/orders/{$order->id}/reorder")->assertStatus(404);
    }

    // -------------------------------------------------------------
    // 6. FORGOT & RESET PASSWORD TESTS (CUSTOMER, SELLER, RIDER)
    // -------------------------------------------------------------

    public function test_forgot_and_reset_password_flows_across_roles(): void
    {
        $roles = [
            'customer' => ['url' => '/api/v1/customer/auth', 'email' => $this->customer->email, 'model' => $this->customer],
            'seller' => ['url' => '/api/v1/seller/auth', 'email' => $this->seller->email, 'model' => $this->seller],
            'rider' => ['url' => '/api/v1/rider/auth', 'email' => $this->rider->email, 'model' => $this->rider],
        ];

        $idx = 1;
        foreach ($roles as $roleName => $data) {
            $prefix = $data['url'];
            $email = $data['email'];
            $model = $data['model'];
            $ip = "10.0.0.{$idx}";
            $idx++;

            // 1. Forgot password
            $forgotRes = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson("{$prefix}/forgot-password", ['email' => $email]);
            $forgotRes->assertStatus(200)->assertJsonPath('success', true);

            // Assert mail sent
            Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use ($email) {
                return $mail->hasTo($email) && strlen($mail->otp) === 6;
            });

            // Assert plain OTP is NOT in Cache, only hash
            $cachedValue = Cache::get("password_reset_otp:{$roleName}:{$email}");
            $this->assertNotNull($cachedValue);
            $this->assertNotEquals(6, strlen($cachedValue));
            $this->assertStringStartsWith('$2y$', $cachedValue);

            // Resend cooldown triggers 429
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson("{$prefix}/forgot-password", ['email' => $email])->assertStatus(429);

            // 2. Wrong OTP fails
            $wrongOtpRes = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson("{$prefix}/reset-password", [
                    'email' => $email,
                    'otp' => '000000',
                    'password' => 'newsecret123',
                    'password_confirmation' => 'newsecret123',
                ]);
            $wrongOtpRes->assertStatus(422)->assertJsonPath('code', 'INVALID_OTP');

            // 3. Reset successfully with correct OTP
            // We can read the OTP from the sent Mailable
            $sentMail = collect(Mail::sent(PasswordResetOtpMail::class))->last();
            $realOtp = $sentMail->otp;

            $resetRes = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson("{$prefix}/reset-password", [
                    'email' => $email,
                    'otp' => $realOtp,
                    'password' => 'newsecret123',
                    'password_confirmation' => 'newsecret123',
                ]);
            $resetRes->assertStatus(200)->assertJsonPath('success', true);

            // Password updated
            $this->assertTrue(Hash::check('newsecret123', $model->fresh()->password));

            // Cache OTP cleared
            $this->assertNull(Cache::get("password_reset_otp:{$roleName}:{$email}"));
        }
    }

    public function test_forgot_password_unknown_email_returns_identical_message(): void
    {
        $res = $this->postJson('/api/v1/customer/auth/forgot-password', [
            'email' => 'nobody_exists@example.com',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'If your email is registered, you will receive a password reset OTP shortly.');
    }

    // -------------------------------------------------------------
    // 7. ACCOUNT DELETION TESTS
    // -------------------------------------------------------------

    public function test_customer_account_deletion_with_active_orders_refused(): void
    {
        Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'Gulshan',
            'phone' => '03009998877',
            'status' => 'pending',
            'total_amount' => 500.00,
        ]);

        Sanctum::actingAs($this->customer, ['customer']);

        $res = $this->deleteJson('/api/v1/customer/account', [
            'password' => 'password123',
        ]);

        $res->assertStatus(422)->assertJsonPath('code', 'HAS_ACTIVE_ORDERS');
    }

    public function test_customer_account_deletion_anonymization_and_cleanup(): void
    {
        // Completed order kept
        $order = Order::create([
            'user_id' => $this->customer->id,
            'seller_id' => $this->seller->id,
            'address' => 'Gulshan',
            'phone' => '03009998877',
            'status' => 'completed',
            'total_amount' => 500.00,
        ]);

        Favorite::create(['user_id' => $this->customer->id, 'seller_id' => $this->seller->id]);
        DeviceToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $this->customer->id,
            'token' => 'ExponentPushToken[CustToken]',
            'platform' => 'android',
        ]);

        Sanctum::actingAs($this->customer, ['customer']);

        $res = $this->deleteJson('/api/v1/customer/account', [
            'password' => 'password123',
        ]);
        $res->assertStatus(200)->assertJsonPath('success', true);

        // Check anonymized user
        $freshCustomer = $this->customer->fresh();
        $this->assertEquals('Deleted user', $freshCustomer->name);
        $this->assertStringStartsWith("deleted-{$this->customer->id}-", $freshCustomer->email);
        $this->assertStringEndsWith('@deleted.invalid', $freshCustomer->email);
        $this->assertNull($freshCustomer->mobile);
        $this->assertNull($freshCustomer->address);
        $this->assertNull($freshCustomer->sector);
        $this->assertFalse((bool) $freshCustomer->is_verified);

        // Orders still exist
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $this->customer->id]);

        // Cleanup: favorites and device tokens deleted
        $this->assertDatabaseMissing('favorites', ['user_id' => $this->customer->id]);
        $this->assertDatabaseMissing('device_tokens', ['tokenable_id' => $this->customer->id]);
    }

    public function test_partner_account_deletion_request_creates_mail_and_enforces_24h_limit(): void
    {
        Sanctum::actingAs($this->seller, ['seller']);

        // 1. Submit request
        $res = $this->postJson('/api/v1/seller/account/deletion-request', [
            'password' => 'password123',
            'reason' => 'Closing physical branch',
        ]);
        $res->assertStatus(202);

        Mail::assertSent(AccountDeletionRequestMail::class, function ($mail) {
            return $mail->role === 'seller' && $mail->accountId === $this->seller->id;
        });

        // 2. Second request within 24 hours refused
        $resDuplicate = $this->postJson('/api/v1/seller/account/deletion-request', [
            'password' => 'password123',
        ]);
        $resDuplicate->assertStatus(422)->assertJsonPath('code', 'DELETION_REQUEST_PENDING');
    }
}
