<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CatalogCategory;
use App\Models\DeviceToken;
use App\Models\Favorite;
use App\Models\GlobalProduct;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

Mail::fake();
Http::fake();

DB::beginTransaction();

try {
    $category = CatalogCategory::create([
        'name' => 'Groceries',
        'slug' => 'groceries',
        'is_active' => true,
    ]);

    $seller = Seller::create([
        'name' => 'Al-Madina Superstore',
        'email' => 'sample_seller@example.com',
        'password' => Hash::make('password123'),
        'city' => 'Karachi',
        'area' => 'Gulshan',
        'sector' => '4A',
        'near_areas' => ['Ali Chowk'],
        'full_address' => 'Shop 10, Sector 4A, Gulshan, Karachi',
        'opens_at' => '08:00:00',
        'closes_at' => '23:00:00',
        'is_open' => true,
        'catalog_category_id' => $category->id,
        'accountIsApproved' => 1,
        'is_deleted' => 0,
    ]);

    $customer = User::create([
        'name' => 'Ayesha Khan',
        'email' => 'ayesha@example.com',
        'password' => Hash::make('password123'),
        'phone' => '03001234567',
        'city' => 'Karachi',
        'sellerType' => 0,
        'is_verified' => true,
    ]);
    $customer->forceFill([
        'sector' => '4A',
        'near_area' => 'Ali Chowk',
    ])->save();

    $rider = Rider::create([
        'name' => 'Tariq Rider',
        'email' => 'tariq@example.com',
        'password' => Hash::make('password123'),
        'phone' => '03120000000',
        'cnic_number' => '42101-1234567-1',
        'vehicle_type' => 'Motorcycle',
        'vehicle_number' => 'KHI-1234',
        'address' => 'Gulshan, Karachi',
        'status' => 'online',
        'is_approved' => true,
        'is_verified' => true,
    ]);

    $globalProduct = GlobalProduct::create([
        'catalog_category_id' => $category->id,
        'name' => 'Basmati Rice 5kg',
        'slug' => 'basmati-rice-5kg',
        'base_price' => 1250.00,
        'unit_type' => 'bag',
        'is_active' => true,
    ]);

    $listing = ShopProduct::create([
        'seller_id' => $seller->id,
        'global_product_id' => $globalProduct->id,
        'stock_quantity' => 15,
        'is_active' => true,
        'price' => 1350.00,
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'seller_id' => $seller->id,
        'address' => 'House 45, Street 2, Gulshan, Karachi',
        'phone' => '03001234567',
        'status' => 'confirmed_by_seller',
        'total_amount' => 1350.00,
    ]);
    $order->forceFill([
        'delivery_sector' => '4A',
        'delivery_near_area' => 'Ali Chowk',
    ])->save();

    OrderItem::create([
        'order_id' => $order->id,
        'shop_product_id' => $listing->id,
        'item_name' => 'Basmati Rice 5kg',
        'unit_type' => 'bag',
        'quantity' => 1,
        'price' => 1350.00,
    ]);

    // Notification sample
    $customer->notify(new OrderStatusNotification($order, 'confirmed_by_seller'));

    // Message samples
    $msg1 = Message::create([
        'order_id' => $order->id,
        'sender_id' => $customer->id,
        'sender_type' => 'user',
        'message' => 'Hello, please pack properly.',
        'is_read' => true,
    ]);

    $msg2 = Message::create([
        'order_id' => $order->id,
        'sender_id' => $seller->id,
        'sender_type' => 'seller',
        'message' => 'Sure Ayesha, we have verified and packed it.',
        'is_read' => false,
    ]);

    // Device token sample
    $deviceToken = DeviceToken::create([
        'tokenable_type' => User::class,
        'tokenable_id' => $customer->id,
        'token' => 'ExponentPushToken[SampleToken12345]',
        'platform' => 'android',
        'device_name' => 'Pixel 7',
        'last_seen_at' => now(),
    ]);

    // Favorite sample
    Favorite::create([
        'user_id' => $customer->id,
        'seller_id' => $seller->id,
    ]);
    Favorite::create([
        'user_id' => $customer->id,
        'shop_product_id' => $listing->id,
    ]);

    $samples = [];

    // Helper for calling route
    $callRoute = function ($method, $uri, $user, $ability, $body = []) {
        Sanctum::actingAs($user, [$ability]);
        $req = Request::create($uri, $method, $body);
        $req->headers->set('Accept', 'application/json');
        return app()->handle($req);
    };

    // 1. GET /api/v1/customer/profile
    $res = $callRoute('GET', '/api/v1/customer/profile', $customer, 'customer');
    $samples['get_customer_profile'] = json_decode($res->getContent(), true);

    // 2. GET /api/v1/customer/favorites/sellers
    $res = $callRoute('GET', '/api/v1/customer/favorites/sellers', $customer, 'customer');
    $samples['get_favorite_sellers'] = json_decode($res->getContent(), true);

    // 3. GET /api/v1/customer/favorites/products
    $res = $callRoute('GET', '/api/v1/customer/favorites/products', $customer, 'customer');
    $samples['get_favorite_products'] = json_decode($res->getContent(), true);

    // 4. POST /api/v1/customer/favorites/sellers/{seller}/toggle
    $res = $callRoute('POST', "/api/v1/customer/favorites/sellers/{$seller->id}/toggle", $customer, 'customer');
    $samples['toggle_favorite_seller'] = json_decode($res->getContent(), true);

    // 5. GET /api/v1/customer/notifications
    $res = $callRoute('GET', '/api/v1/customer/notifications', $customer, 'customer');
    $samples['get_customer_notifications'] = json_decode($res->getContent(), true);

    // 6. GET /api/v1/customer/orders/{order}/reorder
    $res = $callRoute('GET', "/api/v1/customer/orders/{$order->id}/reorder", $customer, 'customer');
    $samples['get_reorder_preview'] = json_decode($res->getContent(), true);

    // 7. GET /api/v1/customer/orders/{order}/messages
    $res = $callRoute('GET', "/api/v1/customer/orders/{$order->id}/messages", $customer, 'customer');
    $samples['get_customer_messages'] = json_decode($res->getContent(), true);

    // 8. POST /api/v1/customer/orders/{order}/messages
    $res = $callRoute('POST', "/api/v1/customer/orders/{$order->id}/messages", $customer, 'customer', ['message' => 'Thank you so much!']);
    $samples['send_customer_message'] = json_decode($res->getContent(), true);

    // 9. POST /api/v1/customer/devices
    $res = $callRoute('POST', '/api/v1/customer/devices', $customer, 'customer', [
        'expo_push_token' => 'ExponentPushToken[NewToken789]',
        'platform' => 'android',
        'device_name' => 'Galaxy S23',
    ]);
    $samples['register_device_token'] = json_decode($res->getContent(), true);

    // 10. POST /api/v1/seller/account/deletion-request
    $res = $callRoute('POST', '/api/v1/seller/account/deletion-request', $seller, 'seller', [
        'password' => 'password123',
        'reason' => 'Relocating business to another region.',
    ]);
    $samples['seller_deletion_request'] = json_decode($res->getContent(), true);

    // 11. POST /api/v1/customer/auth/forgot-password
    $req = Request::create('/api/v1/customer/auth/forgot-password', 'POST', ['email' => 'ayesha@example.com']);
    $req->headers->set('Accept', 'application/json');
    $res = app()->handle($req);
    $samples['forgot_password'] = json_decode($res->getContent(), true);

    file_put_contents(__DIR__ . '/stage8_10_samples.json', json_encode($samples, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "Generated sample responses successfully.\n";
} finally {
    DB::rollBack();
    echo "Transaction rolled back safely. Zero database rows left.\n";
}
