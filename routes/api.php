<?php

use App\Http\Controllers\Api\V1\Auth\AdminAuthController;
use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\RiderAuthController;
use App\Http\Controllers\Api\V1\Auth\SellerAuthController;
use App\Http\Controllers\Api\V1\Customer\CustomerBrowseController;
use App\Http\Controllers\Api\V1\Customer\CustomerFavoriteController;
use App\Http\Controllers\Api\V1\Customer\CustomerNotificationController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderChatController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\CustomerProfileController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\PartnerAccountController;
use App\Http\Controllers\Api\V1\Rider\RiderOrderController;
use App\Http\Controllers\Api\V1\Seller\SellerCatalogController;
use App\Http\Controllers\Api\V1\Seller\SellerNotificationController;
use App\Http\Controllers\Api\V1\Seller\SellerOrderChatController;
use App\Http\Controllers\Api\V1\Seller\SellerOrderController;
use App\Http\Controllers\Api\V1\Seller\SellerVerificationController;
use App\Http\Middleware\Api\EnsureAdminStillActive;
use App\Http\Middleware\Api\EnsureRiderStillApproved;
use App\Http\Middleware\Api\EnsureSellerStillApproved;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Customer Authentication Endpoints
    Route::prefix('customer/auth')->group(function () {
        Route::post('register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('verify-otp', [CustomerAuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
        Route::post('resend-otp', [CustomerAuthController::class, 'resendOtp'])->middleware('throttle:3,1');
        Route::post('login', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('forgot-password', [PasswordResetController::class, 'forgotCustomer'])->middleware('throttle:5,1');
        Route::post('reset-password', [PasswordResetController::class, 'resetCustomer'])->middleware('throttle:5,1');

        Route::middleware(['auth:sanctum', 'ability:customer'])->group(function () {
            Route::post('logout', [CustomerAuthController::class, 'logout']);
            Route::get('me', [CustomerAuthController::class, 'me']);
        });
    });

    // Seller Authentication Endpoints
    Route::prefix('seller/auth')->group(function () {
        Route::post('register', [SellerAuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('login', [SellerAuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('forgot-password', [PasswordResetController::class, 'forgotSeller'])->middleware('throttle:5,1');
        Route::post('reset-password', [PasswordResetController::class, 'resetSeller'])->middleware('throttle:5,1');

        Route::middleware(['auth:sanctum', 'ability:seller'])->group(function () {
            Route::post('logout', [SellerAuthController::class, 'logout']);
            Route::get('me', [SellerAuthController::class, 'me']);
        });
    });

    // Rider Authentication Endpoints
    Route::prefix('rider/auth')->group(function () {
        Route::post('register', [RiderAuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('login', [RiderAuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('forgot-password', [PasswordResetController::class, 'forgotRider'])->middleware('throttle:5,1');
        Route::post('reset-password', [PasswordResetController::class, 'resetRider'])->middleware('throttle:5,1');

        Route::middleware(['auth:sanctum', 'ability:rider'])->group(function () {
            Route::post('logout', [RiderAuthController::class, 'logout']);
            Route::get('me', [RiderAuthController::class, 'me']);
        });
    });

    // Admin Authentication Endpoints
    Route::prefix('admin/auth')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1');

        Route::middleware(['auth:sanctum', 'ability:admin', EnsureAdminStillActive::class])->group(function () {
            Route::post('logout', [AdminAuthController::class, 'logout']);
            Route::get('me', [AdminAuthController::class, 'me']);
        });
    });

    // Customer Browsing Endpoints (Public, throttle:60,1)
    Route::prefix('customer')->middleware('throttle:60,1')->group(function () {
        Route::get('meta/locations', [CustomerBrowseController::class, 'locations']);
        Route::get('home', [CustomerBrowseController::class, 'home']);
        Route::get('sellers/{seller}', [CustomerBrowseController::class, 'sellerDetail']);
        Route::get('sellers/{seller}/products/{listing}', [CustomerBrowseController::class, 'productDetail']);
        Route::get('sellers/{seller}/reviews', [CustomerBrowseController::class, 'reviews']);
        Route::get('search', [CustomerBrowseController::class, 'search']);
    });

    // Customer Cart, Checkout & Orders Endpoints (auth:sanctum, ability:customer)
    Route::prefix('customer')->middleware(['auth:sanctum', 'ability:customer'])->group(function () {
        // Profile & Account Management
        Route::get('profile', [CustomerProfileController::class, 'show']);
        Route::put('profile', [CustomerProfileController::class, 'update']);
        Route::post('profile/password', [CustomerProfileController::class, 'changePassword'])->middleware('throttle:10,1');
        Route::delete('account', [CustomerProfileController::class, 'deleteAccount'])->middleware('throttle:5,1');

        // Customer Favorites
        Route::get('favorites/sellers', [CustomerFavoriteController::class, 'favoriteSellers'])->middleware('throttle:60,1');
        Route::get('favorites/products', [CustomerFavoriteController::class, 'favoriteProducts'])->middleware('throttle:60,1');
        Route::post('favorites/sellers/{seller}/toggle', [CustomerFavoriteController::class, 'toggleSeller'])->whereNumber('seller')->middleware('throttle:30,1');
        Route::post('favorites/products/{listing}/toggle', [CustomerFavoriteController::class, 'toggleProduct'])->whereNumber('listing')->middleware('throttle:30,1');

        // Customer Notifications
        Route::get('notifications', [CustomerNotificationController::class, 'index'])->middleware('throttle:60,1');
        Route::post('notifications/read', [CustomerNotificationController::class, 'markAsRead'])->middleware('throttle:30,1');

        // Orders & Cart
        Route::post('cart/validate', [CustomerOrderController::class, 'validateCart']);
        Route::post('checkout/apply-promo', [CustomerOrderController::class, 'applyPromo'])->middleware('throttle:20,1');
        Route::post('orders', [CustomerOrderController::class, 'placeOrder'])->middleware('throttle:10,1');
        Route::get('orders/active', [CustomerOrderController::class, 'activeOrders']);
        Route::get('orders/history', [CustomerOrderController::class, 'orderHistory']);
        Route::get('orders/{order}', [CustomerOrderController::class, 'show'])->whereNumber('order');
        Route::get('orders/{order}/reorder', [CustomerOrderController::class, 'reorder'])->whereNumber('order');
        Route::post('orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->whereNumber('order');
        Route::post('orders/{order}/feedback', [CustomerOrderController::class, 'feedback'])->whereNumber('order');

        // Customer Order Chat
        Route::get('orders/{order}/messages', [CustomerOrderChatController::class, 'index'])->whereNumber('order');
        Route::post('orders/{order}/messages', [CustomerOrderChatController::class, 'send'])->whereNumber('order')->middleware('throttle:30,1');
        Route::post('orders/{order}/messages/read', [CustomerOrderChatController::class, 'markAsRead'])->whereNumber('order');

        // Customer Device Registration for Push Notifications
        Route::post('devices', [DeviceTokenController::class, 'register'])->middleware('throttle:20,1');
        Route::delete('devices', [DeviceTokenController::class, 'delete']);
    });

    // Rider Fulfillment Endpoints (auth:sanctum, ability:rider, EnsureRiderStillApproved)
    Route::prefix('rider')->middleware(['auth:sanctum', 'ability:rider', EnsureRiderStillApproved::class])->group(function () {
        Route::get('dashboard', [RiderOrderController::class, 'dashboard'])->middleware('throttle:60,1');
        Route::post('status', [RiderOrderController::class, 'updateStatus'])->middleware('throttle:20,1');
        Route::get('orders/available', [RiderOrderController::class, 'availableOrders'])->middleware('throttle:60,1');
        Route::get('orders/current', [RiderOrderController::class, 'currentOrders'])->middleware('throttle:60,1');
        Route::get('orders/{order}', [RiderOrderController::class, 'show'])->whereNumber('order')->middleware('throttle:60,1');
        Route::post('orders/{order}/accept', [RiderOrderController::class, 'accept'])->whereNumber('order')->middleware('throttle:20,1');
        Route::post('orders/{order}/pickup', [RiderOrderController::class, 'pickup'])->whereNumber('order')->middleware('throttle:20,1');
        Route::post('orders/{order}/deliver', [RiderOrderController::class, 'deliver'])->whereNumber('order')->middleware('throttle:10,1');
        Route::get('history', [RiderOrderController::class, 'history'])->middleware('throttle:60,1');

        // Rider Account Deletion Request
        Route::post('account/deletion-request', [PartnerAccountController::class, 'requestRiderDeletion'])->middleware('throttle:3,60');

        // Rider Device Registration for Push Notifications
        Route::post('devices', [DeviceTokenController::class, 'register'])->middleware('throttle:20,1');
        Route::delete('devices', [DeviceTokenController::class, 'delete']);
    });

    // Seller Order Management & Operations (auth:sanctum, ability:seller, EnsureSellerStillApproved)
    Route::prefix('seller')->middleware(['auth:sanctum', 'ability:seller', EnsureSellerStillApproved::class])->group(function () {
        Route::get('dashboard', [SellerOrderController::class, 'dashboard'])->middleware('throttle:60,1');
        Route::get('orders', [SellerOrderController::class, 'index'])->middleware('throttle:60,1');
        Route::get('orders/{order}', [SellerOrderController::class, 'show'])->whereNumber('order')->middleware('throttle:60,1');
        Route::post('orders/{order}/confirm', [SellerOrderController::class, 'confirm'])->whereNumber('order')->middleware('throttle:30,1');
        Route::post('orders/{order}/prepare', [SellerOrderController::class, 'prepare'])->whereNumber('order')->middleware('throttle:30,1');
        Route::post('orders/{order}/ready', [SellerOrderController::class, 'ready'])->whereNumber('order')->middleware('throttle:30,1');
        Route::post('orders/{order}/reject', [SellerOrderController::class, 'reject'])->whereNumber('order')->middleware('throttle:30,1');
        Route::post('orders/{order}/complete', [SellerOrderController::class, 'complete'])->whereNumber('order')->middleware('throttle:30,1');

        // Seller Order Chat
        Route::get('orders/{order}/messages', [SellerOrderChatController::class, 'index'])->whereNumber('order');
        Route::post('orders/{order}/messages', [SellerOrderChatController::class, 'send'])->whereNumber('order')->middleware('throttle:30,1');
        Route::post('orders/{order}/messages/read', [SellerOrderChatController::class, 'markAsRead'])->whereNumber('order');
        Route::get('operating-hours', [SellerOrderController::class, 'getOperatingHours'])->middleware('throttle:60,1');
        Route::put('operating-hours', [SellerOrderController::class, 'updateOperatingHours'])->middleware('throttle:30,1');
        Route::get('earnings', [SellerOrderController::class, 'earnings'])->middleware('throttle:60,1');

        // Seller Notifications
        Route::get('notifications', [SellerNotificationController::class, 'index'])->middleware('throttle:60,1');
        Route::post('notifications/read', [SellerNotificationController::class, 'markAsRead'])->middleware('throttle:30,1');

        // Seller Account Deletion Request
        Route::post('account/deletion-request', [PartnerAccountController::class, 'requestSellerDeletion'])->middleware('throttle:3,60');

        // Seller Device Registration for Push Notifications
        Route::post('devices', [DeviceTokenController::class, 'register'])->middleware('throttle:20,1');
        Route::delete('devices', [DeviceTokenController::class, 'delete']);

        // Seller Catalog Management (throttle: lists 60/m, writes 30/m, bulk-price 6/m)
        Route::prefix('catalog')->group(function () {
            Route::get('listings', [SellerCatalogController::class, 'index'])->middleware('throttle:60,1');
            Route::get('available', [SellerCatalogController::class, 'available'])->middleware('throttle:60,1');
            Route::post('import', [SellerCatalogController::class, 'import'])->middleware('throttle:30,1');
            Route::put('listings/{listing}', [SellerCatalogController::class, 'update'])->whereNumber('listing')->middleware('throttle:30,1');
            Route::post('bulk-price', [SellerCatalogController::class, 'bulkPrice'])->middleware('throttle:6,1');
        });

        // Seller Verification Endpoints
        Route::prefix('verification')->group(function () {
            Route::get('status', [SellerVerificationController::class, 'status'])->middleware('throttle:60,1');
            Route::post('submit', [SellerVerificationController::class, 'submit'])->middleware('throttle:5,1');
        });
    });
});

