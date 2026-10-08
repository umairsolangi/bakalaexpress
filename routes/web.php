<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SellerProductController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SellerVerificationController;
use App\Http\Controllers\RiderAuthController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\SellerCatalogController;
use App\Http\Controllers\Admin\AdminCatalogController;
use App\Http\Controllers\FavoriteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home route
Route::get('/', [HomeController::class, 'showHomePage'])->name('home');

// Auth routes for users
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::get('/verify-otp', [AuthController::class, 'showOtpForm'])->name('verify.otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');

// Auth routes for sellers
Route::get('/seller/register', [SellerController::class, 'showRegisterForm'])->name('register.seller');
Route::post('/seller/register', [SellerController::class, 'register'])->middleware('throttle:5,1');
Route::get('/seller/login', [SellerController::class, 'showLoginForm'])->name('login.seller');
Route::post('/seller/login', [SellerController::class, 'login'])->middleware('throttle:10,1');
Route::get('/seller-panel', [SellerController::class, 'sellerPanel'])->name('seller.panel')->middleware(['auth:seller', 'role:seller']);

// Logout routes
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/seller/logout', [SellerController::class, 'logout'])->name('logout.seller');


// Rider Auth Routes
Route::get('/rider/login', [RiderAuthController::class, 'showLoginForm'])->name('rider.login');
Route::post('/rider/login', [RiderAuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/rider/register', [RiderAuthController::class, 'showRegisterForm'])->name('rider.register');
Route::post('/rider/register', [RiderAuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/rider/logout', [RiderAuthController::class, 'logout'])->name('rider.logout');

// Rider Dashboard & Routes
Route::middleware(['auth:rider', 'role:rider', 'approved_rider'])->group(function () {
    Route::get('/rider/dashboard', [RiderController::class, 'dashboard'])->name('rider.dashboard');
    Route::post('/rider/toggle-status', [RiderController::class, 'toggleStatus'])->name('rider.toggle-status');
    Route::get('/rider/available-orders', [RiderController::class, 'availableOrders'])->name('rider.available-orders');
    Route::post('/rider/order/{order}/accept', [RiderController::class, 'acceptOrder'])->name('rider.order.accept');
    Route::post('/rider/order/{order}/update-status', [RiderController::class, 'updateStatus'])->name('rider.order.update-status');
});

// Seller Product Routes (Refactored)
Route::middleware(['auth:seller', 'role:seller'])->group(function () {
    Route::put('/seller/products/{id}', [SellerProductController::class, 'update'])->name('seller.updateService'); // Legacy name kept for safety
    Route::get('/seller/add-product', [SellerProductController::class, 'showAddProductForm'])->name('add.service'); // Legacy name kept
    Route::post('/seller/add-product', [SellerProductController::class, 'storeProduct'])->name('store.service'); // Legacy name kept
    Route::get('/seller/edit-product/{id}', [SellerProductController::class, 'edit'])->name('seller.editService'); // Legacy name kept
    Route::delete('/seller/delete-product/{id}', [SellerProductController::class, 'delete'])->name('seller.deleteService'); // Legacy name kept

    // Earnings feature
    Route::get('/seller/earnings', [SellerController::class, 'earnings'])->name('seller.earnings');
    Route::post('/seller/operating-hours', [SellerController::class, 'updateOperatingHours'])->name('seller.operating-hours.update');
    Route::get('/seller/catalog', [SellerCatalogController::class, 'index'])->name('seller.catalog.index');
    Route::post('/seller/catalog/import', [SellerCatalogController::class, 'importCategoryCatalog'])->name('seller.catalog.import');
    Route::post('/seller/catalog/bulk-update', [SellerCatalogController::class, 'bulkUpdate'])->name('seller.catalog.bulk-update');
    Route::put('/seller/catalog/listings/{listing}', [SellerCatalogController::class, 'updateListing'])->name('seller.catalog.listings.update');
});

// Seller Verification routes
Route::middleware(['auth:seller', 'role:seller'])->group(function () {
    Route::get('/seller/verification/apply', [SellerVerificationController::class, 'showVerificationForm'])->name('seller.verification.apply');
    Route::post('/seller/verification/submit', [SellerVerificationController::class, 'submitVerificationRequest'])->name('seller.verification.submit');
    Route::get('/seller/verification/status', [SellerVerificationController::class, 'showVerificationStatus'])->name('seller.verification.status');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/moderation-queue', [AdminController::class, 'moderationQueue'])->name('admin.moderation.queue');
    Route::post('/admin/moderation-queue/bulk', [AdminController::class, 'bulkModerationAction'])->name('admin.moderation.bulk');
    Route::post('/admin/approve-seller/{id}', [AdminController::class, 'approveSeller'])->name('admin.approveSeller');
    Route::post('/admin/reject-seller/{id}', [AdminController::class, 'rejectSeller'])->name('admin.rejectSeller');
    Route::get('/admin/pending-products', [AdminController::class, 'view'])->name('admin.pending-products');
    Route::post('/admin/approve-product/{id}', [AdminController::class, 'approveProduct'])->name('admin.approveProduct');
    Route::post('/admin/reject-product/{id}', [AdminController::class, 'rejectProduct'])->name('admin.rejectProduct');
    Route::get('/admin/catalog', [AdminCatalogController::class, 'index'])->name('admin.catalog.index');
    Route::post('/admin/catalog/categories', [AdminCatalogController::class, 'storeCategory'])->name('admin.catalog.categories.store');
    Route::put('/admin/catalog/categories/{category}', [AdminCatalogController::class, 'updateCategory'])->name('admin.catalog.categories.update');
    Route::post('/admin/catalog/products', [AdminCatalogController::class, 'storeProduct'])->name('admin.catalog.products.store');
    Route::put('/admin/catalog/products/{product}', [AdminCatalogController::class, 'updateProduct'])->name('admin.catalog.products.update');
    Route::delete('/admin/catalog/products/{product}', [AdminCatalogController::class, 'destroyProduct'])->name('admin.catalog.products.destroy');
    Route::get('/admin/sellers', [AdminController::class, 'manageSellers'])->name('admin.sellers');
    Route::get('/admin/products', [AdminController::class, 'manageProducts'])->name('admin.products');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/promo-codes', [AdminController::class, 'storePromoCode'])->name('admin.promo-codes.store');
    Route::post('/admin/login-seller/{id}', [AdminController::class, 'loginAsSeller'])->name('admin.loginSeller');
    Route::post('/admin/return-to-admin', [AdminController::class, 'returnToAdmin'])->name('admin.returnToAdmin');

    // Admin Verification Management
    Route::get('/admin/verifications', [\App\Http\Controllers\Admin\VerificationController::class, 'index'])->name('admin.verifications.index');
    Route::get('/admin/verifications/{verification}', [\App\Http\Controllers\Admin\VerificationController::class, 'show'])->name('admin.verifications.show');
    Route::post('/admin/verifications/{verification}/approve', [\App\Http\Controllers\Admin\VerificationController::class, 'approve'])->name('admin.verifications.approve');
    Route::post('/admin/verifications/{verification}/reject', [\App\Http\Controllers\Admin\VerificationController::class, 'reject'])->name('admin.verifications.reject');

    // Admin Rider Management
    Route::get('/admin/riders/pending', [\App\Http\Controllers\Admin\RiderController::class, 'index'])->name('admin.riders.pending');
    Route::post('/admin/riders/{id}/approve', [\App\Http\Controllers\Admin\RiderController::class, 'approve'])->name('admin.rider.approve');
    Route::post('/admin/riders/{id}/reject', [\App\Http\Controllers\Admin\RiderController::class, 'reject'])->name('admin.rider.reject');
});

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('notifications/{id}/redirect', [NotificationController::class, 'redirectToService'])->name('notifications.redirect');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
});

// Product Viewing Routes (Refactored)
Route::get('/sellers/{seller}', [ProductController::class, 'showSellerProducts'])->name('sellers.services');
Route::get('sellers/{sellerId}/services', [ProductController::class, 'showSellerProducts'])->name('sellers.services.alias');
Route::get('/sellers/{seller}/products/{listing}', [ProductController::class, 'showCatalogProduct'])->name('catalog.product.show');
Route::get('products/{id}', [ProductController::class, 'showProduct'])->name('service.show');
Route::get('/search-products', [ProductController::class, 'searchProducts'])->name('search.services');

// Notification route
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

// Routes for cart functionality (Refactored to Product model logic internally)
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.view');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// Routes for order placement and tracking (protected by 'auth')
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/checkout', [OrderController::class, 'showCheckout'])->name('checkout.show');
    Route::post('/checkout/promo', [OrderController::class, 'applyPromo'])->name('checkout.promo');
    Route::post('/checkout', [OrderController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/orders/{order}/track', [OrderController::class, 'track'])->name('order.track');
    Route::get('/order/track/{order}', [OrderController::class, 'trackOrder'])->name('order.track.legacy');
    Route::post('/order/{order}/accept-reject', [OrderController::class, 'acceptRejectOrder'])->name('order.acceptReject');
    Route::post('/orders/{order}/buy-again', [OrderController::class, 'buyAgain'])->name('order.buyAgain');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');
    Route::get('/order/history', [OrderController::class, 'history'])->name('order.history');
    Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
    Route::post('/favorites/sellers/{seller}', [FavoriteController::class, 'toggleSeller'])->name('favorites.sellers.toggle');
    Route::post('/favorites/products/{listing}', [FavoriteController::class, 'toggleProduct'])->name('favorites.products.toggle');
});

// Order Handling for Sellers
Route::post('/seller/order/{order}/update-status', [OrderController::class, 'updateOrderStatus'])
    ->name('order.updateStatus')
    ->middleware(['auth:seller', 'role:seller']);
Route::get('/seller/order/{order}/handle', [OrderController::class, 'handleOrder'])
    ->name('seller.order.handle')
    ->middleware(['auth:seller', 'role:seller']);

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/orders', [OrderController::class, 'allOrders'])->name('order.all');
});

// Legacy route support (Redirect or Alias?)
// Route::get('/sellers/{seller_id}/services', [SellerController::class, 'showServices'])->name('seller.services'); 
// Disabled above as it points to SellerController::showServices which might not exist or be needed.

// User (buyer) chat routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/chat/{order}', [MessageController::class, 'chat'])->name('chat.index');
    Route::post('/chat/{order}/send', [MessageController::class, 'send'])->name('chat.send');
    Route::post('/chat/{order}/read', [MessageController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/chat/{order}/messages', [MessageController::class, 'getMessages'])->name('chat.get-messages');
});

// Seller chat routes
Route::middleware(['auth:seller', 'role:seller'])->group(function () {
    Route::get('/seller/chat/{order}', [MessageController::class, 'chat'])->name('seller.chat.index');
    Route::post('/seller/chat/{order}/send', [MessageController::class, 'send'])->name('seller.chat.send');
    Route::post('/seller/chat/{order}/read', [MessageController::class, 'markAsRead'])->name('seller.chat.mark-read');
    Route::get('/seller/chat/{order}/messages', [MessageController::class, 'getMessages'])->name('seller.chat.get-messages');
});

Route::get('/order/{id}/feedback', [OrderController::class, 'feedback'])->name('order.feedback');
Route::post('/order/{id}/feedback', [OrderController::class, 'submitFeedback'])->name('order.feedback.submit');
