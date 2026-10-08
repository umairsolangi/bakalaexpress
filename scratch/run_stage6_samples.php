<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CatalogCategory;
use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

DB::beginTransaction();

try {
    $category = CatalogCategory::firstOrCreate(
        ['slug' => 'sample-supermarket'],
        ['name' => 'Sample Supermarket', 'is_active' => true]
    );

    $seller = Seller::create([
        'name' => 'Al-Madina Mart Sample',
        'email' => 'sample_seller_' . uniqid() . '@example.com',
        'password' => Hash::make('password123'),
        'city' => 'Karachi',
        'area' => 'Gulshan-e-Iqbal',
        'sector' => 'Block 5',
        'full_address' => 'Shop 12, Commercial Area, Block 5, Gulshan, Karachi',
        'opens_at' => '08:00:00',
        'closes_at' => '23:00:00',
        'is_open' => true,
        'catalog_category_id' => $category->id,
        'accountIsApproved' => 1,
        'is_deleted' => 0,
    ]);

    $customer = User::create([
        'name' => 'Zainab Sample',
        'email' => 'sample_customer_' . uniqid() . '@example.com',
        'password' => Hash::make('password123'),
        'phone' => '03001234567',
        'city' => 'Karachi',
        'sellerType' => 0,
        'is_verified' => true,
    ]);

    $rider = Rider::create([
        'name' => 'Rashid Sample Rider',
        'email' => 'sample_rider_' . uniqid() . '@example.com',
        'password' => Hash::make('password123'),
        'phone' => '03111234567',
        'cnic_number' => '42101-1234567-9',
        'vehicle_type' => 'Motorcycle',
        'vehicle_number' => 'KHI-9988',
        'address' => 'Gulshan-e-Iqbal, Karachi',
        'status' => 'online',
        'is_approved' => true,
        'is_verified' => true,
    ]);

    $globalProduct = GlobalProduct::firstOrCreate(
        ['name' => 'Sample Fresh Milk 1L'],
        ['catalog_category_id' => $category->id, 'base_price' => 280.00, 'is_active' => true]
    );

    $shopProduct = ShopProduct::create([
        'seller_id' => $seller->id,
        'global_product_id' => $globalProduct->id,
        'custom_price' => 280.00,
        'stock_quantity' => 20,
        'is_active' => true,
    ]);

    // Create a pending order
    $pendingOrder = Order::create([
        'user_id' => $customer->id,
        'seller_id' => $seller->id,
        'address' => 'Apartment 4B, Hill View Heights, Sector F-8, Islamabad',
        'phone' => '03001234567',
        'status' => 'pending',
        'total_amount' => 560.00,
    ]);
    DB::table('orders')->where('id', $pendingOrder->id)->update([
        'delivery_charges' => 0.00,
        'discount_amount' => 0.00,
        'delivery_instructions' => 'Call customer upon arrival.',
    ]);
    OrderItem::create([
        'order_id' => $pendingOrder->id,
        'shop_product_id' => $shopProduct->id,
        'global_product_id' => $globalProduct->id,
        'item_name' => 'Sample Fresh Milk 1L',
        'unit_type' => 'piece',
        'quantity' => 2,
        'price' => 280.00,
    ]);

    // Create a completed order for earnings/dashboard
    $completedOrder = Order::create([
        'user_id' => $customer->id,
        'seller_id' => $seller->id,
        'address' => 'Apartment 4B, Hill View Heights, Sector F-8, Islamabad',
        'phone' => '03001234567',
        'status' => 'completed',
        'total_amount' => 1120.00,
    ]);
    DB::table('orders')->where('id', $completedOrder->id)->update([
        'delivery_charges' => 0.00,
        'discount_amount' => 0.00,
        'rider_id' => $rider->id,
    ]);

    // Create a delivered order ready to be completed
    $deliveredOrder = Order::create([
        'user_id' => $customer->id,
        'seller_id' => $seller->id,
        'address' => 'Apartment 4B, Hill View Heights, Sector F-8, Islamabad',
        'phone' => '03001234567',
        'status' => 'delivered',
        'total_amount' => 560.00,
    ]);
    DB::table('orders')->where('id', $deliveredOrder->id)->update([
        'delivery_charges' => 0.00,
        'discount_amount' => 0.00,
        'rider_id' => $rider->id,
    ]);

    $token = $seller->createToken('seller-token', ['seller'])->plainTextToken;

    $call = function (string $method, string $uri, array $data = []) use ($app, $token) {
        $server = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];
        $req = Request::create($uri, $method, [], [], [], $server, json_encode($data));
        $res = $app->handle($req);
        return [
            'status' => $res->getStatusCode(),
            'body' => json_decode($res->getContent(), false),
        ];
    };

    $samples = [];

    // 1. Dashboard
    $samples['get_seller_dashboard'] = $call('GET', '/api/v1/seller/dashboard');

    // 2. Orders list (all)
    $samples['get_seller_orders_all'] = $call('GET', '/api/v1/seller/orders');

    // 3. Orders list (pending group)
    $samples['get_seller_orders_pending'] = $call('GET', '/api/v1/seller/orders?group=pending');

    // 4. Order detail
    $samples['get_seller_order_detail'] = $call('GET', "/api/v1/seller/orders/{$pendingOrder->id}");

    // 5. Confirm order
    $samples['post_seller_order_confirm'] = $call('POST', "/api/v1/seller/orders/{$pendingOrder->id}/confirm");

    // 6. Prepare order
    $samples['post_seller_order_prepare'] = $call('POST', "/api/v1/seller/orders/{$pendingOrder->id}/prepare");

    // 7. Ready order
    $samples['post_seller_order_ready'] = $call('POST', "/api/v1/seller/orders/{$pendingOrder->id}/ready");

    // Create a new order to reject
    $rejectOrder = Order::create([
        'user_id' => $customer->id,
        'seller_id' => $seller->id,
        'address' => 'Apartment 4B, Hill View Heights, Sector F-8, Islamabad',
        'phone' => '03001234567',
        'status' => 'pending',
        'total_amount' => 280.00,
    ]);
    OrderItem::create([
        'order_id' => $rejectOrder->id,
        'shop_product_id' => $shopProduct->id,
        'item_name' => 'Sample Fresh Milk 1L',
        'quantity' => 1,
        'price' => 280.00,
    ]);

    // 8. Reject order
    $samples['post_seller_order_reject'] = $call('POST', "/api/v1/seller/orders/{$rejectOrder->id}/reject", [
        'reason' => 'Item is currently out of stock at this location.',
    ]);

    // 9. Complete order
    $samples['post_seller_order_complete'] = $call('POST', "/api/v1/seller/orders/{$deliveredOrder->id}/complete");

    // 10. Operating hours GET
    $samples['get_seller_operating_hours'] = $call('GET', '/api/v1/seller/operating-hours');

    // 11. Operating hours PUT
    $samples['put_seller_operating_hours'] = $call('PUT', '/api/v1/seller/operating-hours', [
        'is_open' => true,
        'opens_at' => '09:00',
        'closes_at' => '22:30',
    ]);

    // 12. Earnings GET
    $samples['get_seller_earnings'] = $call('GET', '/api/v1/seller/earnings');

    file_put_contents(
        __DIR__ . '/stage6_sample_responses.json',
        json_encode($samples, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    echo "Samples successfully generated and written to stage6_sample_responses.json\n";

} finally {
    DB::rollBack();
    echo "Transaction rolled back safely. Local database remains clean.\n";
}
