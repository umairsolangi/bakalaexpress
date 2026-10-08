# Bakala Express — Web Code Bugs and Audit Findings

This document tracks bugs, discrepancies, and security vulnerabilities identified in the existing web application code. As instructed by project governance, these are documented here for tracking and have NOT been modified in the web codebase.

---

### 1. Unverified Customer Can Bypass OTP and Log In
- **File:** [AuthController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/AuthController.php#L142-L155)
- **Problem:** `AuthController::login()` validates credentials using `Auth::attempt($credentials)` and logs the user in without checking `is_verified`. A customer can register, skip email OTP verification entirely, and immediately log into the web application.
- **Fix Idea:** After successful `Auth::attempt()`, check `if (!Auth::user()->is_verified) { Auth::logout(); return back()->withErrors(['login' => 'Please verify your OTP first.']); }`.

---

### 2. Soft-Deleted Sellers Allowed to Log In
- **File:** [SellerController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/SellerController.php#L133-L145)
- **Problem:** `SellerController::login()` checks `$seller->accountIsApproved == 0`, but does not check `$seller->is_deleted`. When an administrator soft-deletes a merchant account via `rejectSeller()`, the seller can still authenticate and access the panel.
- **Fix Idea:** Add check: `if ($seller->is_deleted) { return back()->withErrors(['email' => 'This merchant account has been deactivated.']); }`.

---

### 3. Inventory Leak on Customer Order Cancellation
- **File:** [OrderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/OrderController.php#L573-L596)
- **Problem:** When a seller accepts an order, `reserveCatalogInventory()` decrements stock from `shop_product` and stamps `inventory_reserved_at`. If the seller rejects the order, `restoreCatalogInventory()` restores stock. However, if the customer cancels an order that was in `confirmed_by_seller` state via `cancel()`, the order is marked `cancelled` without calling `restoreCatalogInventory()`. The merchant's inventory is permanently lost.
- **Fix Idea:** In `OrderController::cancel()`, check `if ($order->inventory_reserved_at) { $this->restoreCatalogInventory($order); }`.

---

### 4. Missing Auth Middleware on Order Review / Feedback Submission
- **File:** [routes/web.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/routes/web.php#L205) & [OrderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/OrderController.php#L737-L760)
- **Problem:** `POST /order/{id}/feedback` is defined outside the `auth` middleware group in `web.php`. The controller method `submitFeedback()` calls `auth()->id()`. If an unauthenticated guest requests this route, `auth()->id()` evaluates to `null`, causing an unhandled database exception or creating orphaned reviews.
- **Fix Idea:** Protect the route with `['auth', 'role:user']` in `web.php`.

---

### 5. Missing `rider_id` in Order Model `$fillable` & Missing Relationship
- **File:** [Order.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Models/Order.php#L15-L35)
- **Problem:** `App\Models\Order` does not list `'rider_id'` in `$fillable`. Using `$order->update(['rider_id' => $riderId])` or `$order->fill()` silently discards `rider_id`. Also, the `Order` model does not have a `public function rider()` relationship.
- **Fix Idea:** Add `'rider_id'` to `$fillable` in `Order.php` and define `public function rider() { return $this->belongsTo(Rider::class); }`.

---

### 6. Rider Documents Uploaded Without Sanitization to Public Storage
- **File:** [RiderAuthController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/RiderAuthController.php#L65-L74)
- **Problem:** Rider KYC files (`cnic_front`, `cnic_back`, `license_image`, `vehicle_image`, `registration_book`) are stored directly via `$request->file(...)->store('rider_docs/...', 'public')`. These files bypass image sanitization (unlike seller avatars), and are placed in the publicly accessible storage folder (`public/storage/rider_docs/`), exposing sensitive national identity documents (CNIC, driving license) to public scraping.
- **Fix Idea:** Process uploaded images through `SanitizedImageUpload` to strip EXIF data and malicious code, and store identity documents on a private storage disk with authenticated, signed-URL streaming for administrators.

---

### 7. Realtime Chat Broadcasting Commented Out
- **File:** [MessageController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/MessageController.php#L75)
- **Problem:** Line 75 contains `// event(new NewMessage($message, $order));`. Real-time broadcasting via Pusher is disabled, forcing the web client into continuous polling.
- **Fix Idea:** Uncomment the broadcast dispatch or use a background queue job if Pusher latency was the concern.

---

### 8. `SellerEmailValidationTest` Uses Dummy Bytes Instead of Valid Image
- **File:** [SellerEmailValidationTest.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/tests/Feature/SellerEmailValidationTest.php#L147)
- **Problem:** The test helper `createTestImage()` uses `UploadedFile::fake()->create('profile.jpg', 100, 'image/jpeg')`. This produces zero-byte dummy file data rather than a valid image structure. Because `SellerController@register` routes images through `SanitizedImageUpload`, `getimagesizefromstring` fails.
- **Fix Idea:** Change `UploadedFile::fake()->create(...)` to `UploadedFile::fake()->image('profile.jpg', 100, 100)`.

---

### 9. Rider Dashboard Calculates Trip Earnings from Active Orders Instead of Delivered Orders
- **File:** [RiderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/RiderController.php#L34)
- **Problem:** `$currentEarnings = $activeOrders->sum('delivery_charges');` sums delivery charges from active orders currently in flight (`assigned_to_rider`, `picked_up`). Once a rider delivers an order, it is excluded from `$activeOrders`, causing the displayed earnings to drop back to zero instead of accumulating delivered earnings.
- **Fix Idea:** Calculate daily earnings from delivered and completed orders: `Order::where('rider_id', $rider->id)->whereIn('status', ['delivered', 'completed'])->whereDate('updated_at', now()->toDateString())->sum('delivery_charges')`.

---

### 10. Offline Riders Can Accept Orders on Web
- **File:** [RiderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/RiderController.php#L54-L81)
- **Problem:** `RiderController::acceptOrder()` checks `is_approved`, but does not verify whether `$rider->status === 'online'`. Even though the Blade UI hides the list when offline, any offline rider can directly submit a POST request to accept orders without going online.
- **Fix Idea:** Add check: `if ($rider->status !== 'online') { return back()->with('error', 'You must be online to accept orders.'); }`.

---

### 11. No Concurrency Limit on Active Orders Held by One Rider
- **File:** [RiderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/RiderController.php#L54-L81)
- **Problem:** The web application imposes no cap on how many concurrent active orders a single rider can claim. A single rider could accept dozens of orders simultaneously, causing order hoarding and massive customer delivery delays.
- **Fix Idea:** Enforce a maximum active order limit (e.g. 2 or 3 active orders per rider) before permitting order acceptance.

---

### 12. Delivery Proof Upload Bypasses Sanitization in Web Controller
- **File:** [RiderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/RiderController.php#L106)
- **Problem:** `RiderController::updateStatus()` uses raw `$request->file('delivery_proof_image')->store('delivery_proofs', 'public')`. Unlike seller avatar uploads, proof photos bypass `SanitizedImageUpload`, potentially preserving EXIF metadata and malicious payloads in public storage.
- **Fix Idea:** Process proof images through `SanitizedImageUpload::storeProfileImage($file, 'delivery_proofs')`.

---

### 13. Customer Full Delivery Address Exposed to All Riders Prior to Acceptance
- **File:** [dashboard.blade.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/resources/views/rider/dashboard.blade.php#L320)
- **Problem:** The available requests list displays the customer's full doorstep address (`$order->address`) to every online rider before the order has been accepted.
- **Fix Idea:** Display only general area/neighborhood hints (e.g., sector or area) before acceptance, and reveal full doorstep address and customer contact details only after a rider has accepted the order.

---

### 14. `POST /order/{order}/accept-reject` Route Placed Under Customer Role Instead of Seller
- **File:** [routes/web.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/routes/web.php#L204) & [OrderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/OrderController.php#L684-L709)
- **Problem:** `POST /order/{order}/accept-reject` is wired to `OrderController::acceptOrRejectOrder` inside the `['auth', 'role:user']` middleware group in `web.php`. Because it is gated by the `user` (customer) role instead of `seller`, regular customers can hit this endpoint, while authenticated sellers without the user role cannot access it cleanly. Furthermore, the controller method checks `auth()->id() !== $order->seller_id`, which fails if the logged-in customer is not a seller.
- **Fix Idea:** Move this route to the seller route group protected by seller authentication middleware, or deprecate it in favor of the dedicated seller panel order handler.

---

### 15. Web SellerCatalogController Allows Editing Deactivated or Soft-Deleted Products
- **File:** [SellerCatalogController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/SellerCatalogController.php#L80-L99)
- **Problem:** While `SellerCatalogController::index()` filters out inactive global products with `whereHas('globalProduct', fn($q) => $q->where('is_active', true))`, the `updateListing()` method only checks `$listing->seller_id !== $sellerId`. It fails to verify if the underlying `GlobalProduct` is active or soft-deleted. A seller can continue altering custom prices and increasing inventory for products an admin has pulled from the platform.
- **Fix Idea:** In `SellerCatalogController::updateListing()`, check `if (!$listing->globalProduct || !$listing->globalProduct->is_active) { return back()->with('error', 'Product is unavailable.'); }`.

---

### 16. Web Bulk Update Allows Price Decreases Leading to Zero or Negative Product Prices
- **File:** [SellerCatalogController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/SellerCatalogController.php#L107-L163)
- **Problem:** `SellerCatalogController::bulkUpdate()` accepts `percent_value` up to `500` for both `increase_percent` and `decrease_percent`. A decrease of 100% reduces prices to 0.00, and a decrease between 101% and 500% results in negative prices in the database (`ROUND(price * -0.5, 2)`).
- **Fix Idea:** Restrict `decrease_percent` to a maximum of 50% or enforce a `HAVING calculated_price > 0` / minimum price floor (e.g. PKR 1.00) in the SQL update.

---

### 17. Seller Not Notified on Web Customer Order Cancellation
- **File:** [OrderController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/OrderController.php#L573-L596)
- **Problem:** When a customer cancels an order on the web, `OrderController::cancel()` updates status to `cancelled` and flashes a session message to the customer. However, it sends NO notification (database notification, email, or event) to the merchant/seller. The seller is completely unaware that the order was cancelled by the customer until they manually refresh their panel.
- **Fix Idea:** Dispatch a notification or email to `$order->seller` when a customer cancels an order.

---

### 18. Web Chat Allows Indefinite Messaging on Closed Orders Without Lifecycle Expiry
- **File:** [MessageController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/MessageController.php#L42-L84)
- **Problem:** `MessageController::send()` verifies that the caller is the order's user or seller, but enforces zero checks on the order status or closed timestamp. Users can send chat messages indefinitely on orders that were delivered or cancelled months or years ago.
- **Fix Idea:** Add a lifecycle check rejecting messages on terminal orders once the post-close window (e.g. 48 hours) expires.

---

### 19. Web Message Controller Has No Maximum Character Length Validation
- **File:** [MessageController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/MessageController.php#L44-L46)
- **Problem:** `MessageController::send()` validates `'message' => 'required|string'` without any `max` constraint. A client can send megabytes of text in a single request, creating database bloat and UI rendering issues.
- **Fix Idea:** Enforce `'max:1000'` on message content.

---

### 20. Web Profile Update Does Not Clean Up Old Avatar Image Files on Disk
- **File:** [ProfileController.php](file:///c:/laragon/www/bakalaandshispare/bakalaexpressbackend/bakalaexpressbackend/app/Http/Controllers/ProfileController.php#L35-L65)
- **Problem:** When a customer uploads a new profile picture, `SanitizedImageUpload` creates a new file in `public/profile_images/` and stores a record in `user_profile_updates`. However, the old avatar image file on disk is never deleted, leading to storage leakage over time.
- **Fix Idea:** Delete the previous avatar file from `Storage::disk('public')` before persisting the new one.



