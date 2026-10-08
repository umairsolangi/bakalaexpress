<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Rider;
use App\Models\Feedback;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Notifications\ServiceApprovedNotification;

class AdminController extends Controller
{
    private function ensureAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && (int) ($user->sellerType ?? 0) === 1, 403, 'Unauthorized admin action.');
    }

    public function view()
    {
        $this->ensureAdmin();
        $pendingProducts = Product::where('is_approved', 0)->get();
        return view('admin.pending-products', compact('pendingProducts'));
    }

    public function approveProduct($id)
    {
        $this->ensureAdmin();
        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('admin.dashboard')->with('error', 'Product not found.');
        }

        $product->is_approved = 1; 
        $product->save();
        
        // Get the seller associated with this product
        $seller = Seller::find($product->seller_id);
        if ($seller) {
            try {
                $seller->notify(new ServiceApprovedNotification($product->name, $product->id));
            } catch (\Throwable $e) {
                Log::warning('Product approval notification failed', [
                    'seller_id' => $seller->id,
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('admin.dashboard')->with('status', 'Product approved successfully.');
    }

    public function rejectProduct($id)
    {
        $this->ensureAdmin();
        $product = Product::find($id);
        if ($product) {
             $product->delete(); // Alternatively, mark as rejected if needed
             return redirect()->route('admin.dashboard')->with('status', 'Product rejected successfully.');
        }
        return redirect()->route('admin.dashboard')->with('error', 'Product not found.');
    }

    public function approveSeller($id)
    {
        $this->ensureAdmin();
        $seller = Seller::find($id);
        $seller->accountIsApproved = 1;
        $seller->save();

        return redirect()->route('admin.dashboard')->with('status', 'Seller approved successfully.');
    }

    public function dashboard()
    {
        $this->ensureAdmin();
        $pendingSellers = Seller::where('accountIsApproved', 0)->where('is_deleted', 0)->get();
        $pendingProducts = Product::where('is_approved', 0)->get();
        $sellers = Seller::where('accountIsApproved', 1)->where('is_deleted', 0)->get();

        // Calculate business statistics
        $totalUsers = User::count();
        $totalSellers = Seller::where('is_deleted', 0)->count();
        $totalRiders = Rider::count();
        $totalOrders = Order::count();
        $totalRevenue = (float) Order::whereIn('status', ['completed', 'delivered'])->sum('total_amount');

        // Order history for last 7 days (Chart.js data)
        $orderChartLabels = [];
        $orderChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $orderChartLabels[] = $date->format('M d');
            $orderChartData[] = Order::whereDate('created_at', $date->toDateString())->count();
        }

        // Recent Orders and Users
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'pendingSellers',
            'pendingProducts',
            'sellers',
            'totalUsers',
            'totalSellers',
            'totalRiders',
            'totalOrders',
            'totalRevenue',
            'orderChartLabels',
            'orderChartData',
            'recentOrders',
            'recentUsers'
        ));
    }

    public function moderationQueue(Request $request)
    {
        $this->ensureAdmin();

        $tab = $request->get('tab', 'sellers');
        if (!in_array($tab, ['sellers', 'riders', 'products', 'reviews'], true)) {
            $tab = 'sellers';
        }

        $search = trim((string) $request->get('search', ''));

        $pendingSellers = Seller::query()
            ->where('accountIsApproved', 0)
            ->where('is_deleted', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendingRiders = Rider::query()
            ->where('is_approved', false)
            ->where('status', '!=', 'rejected')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendingProducts = Product::query()
            ->where('is_approved', 0)
            ->with('seller')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                            $sellerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendingReviews = Feedback::query()
            ->where('status', 'pending')
            ->with(['user', 'seller', 'order'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('feedback', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('seller', fn ($sellerQuery) => $sellerQuery->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'sellers' => Seller::where('accountIsApproved', 0)->where('is_deleted', 0)->count(),
            'riders' => Rider::where('is_approved', false)->where('status', '!=', 'rejected')->count(),
            'products' => Product::where('is_approved', 0)->count(),
            'reviews' => Feedback::where('status', 'pending')->count(),
        ];

        return view('admin.moderation-queue', compact(
            'tab',
            'search',
            'pendingSellers',
            'pendingRiders',
            'pendingProducts',
            'pendingReviews',
            'counts'
        ));
    }

    public function bulkModerationAction(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'tab' => 'required|in:sellers,riders,products,reviews',
            'action' => 'required|in:approve,reject',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'search' => 'nullable|string|max:100',
        ]);

        $tab = $validated['tab'];
        $action = $validated['action'];
        $ids = $validated['ids'];
        $affected = 0;

        if ($tab === 'sellers') {
            if ($action === 'approve') {
                $affected = Seller::whereIn('id', $ids)
                    ->where('accountIsApproved', 0)
                    ->where('is_deleted', 0)
                    ->update(['accountIsApproved' => 1]);
            } else {
                $affected = Seller::whereIn('id', $ids)
                    ->where('accountIsApproved', 0)
                    ->where('is_deleted', 0)
                    ->update(['is_deleted' => 1]);
            }
        }

        if ($tab === 'riders') {
            if ($action === 'approve') {
                $affected = Rider::whereIn('id', $ids)
                    ->where('is_approved', false)
                    ->update(['is_approved' => true]);
            } else {
                $affected = Rider::whereIn('id', $ids)
                    ->where('is_approved', false)
                    ->delete();
            }
        }

        if ($tab === 'products') {
            if ($action === 'approve') {
                $affected = Product::whereIn('id', $ids)
                    ->where('is_approved', 0)
                    ->update(['is_approved' => 1]);
            } else {
                $affected = Product::whereIn('id', $ids)
                    ->where('is_approved', 0)
                    ->delete();
            }
        }

        if ($tab === 'reviews') {
            $affected = Feedback::whereIn('id', $ids)
                ->where('status', 'pending')
                ->update([
                    'status' => $action === 'approve' ? 'approved' : 'rejected',
                    'moderated_at' => now(),
                    'moderated_by' => Auth::id(),
                ]);
        }

        if ($affected === 0) {
            return redirect()->route('admin.moderation.queue', [
                'tab' => $tab,
                'search' => $validated['search'] ?? null,
            ])->with('error', 'No valid pending records were updated.');
        }

        $entityLabel = ucfirst(substr($tab, 0, -1));
        $actionLabel = $action === 'approve' ? 'approved' : 'rejected';

        return redirect()->route('admin.moderation.queue', [
            'tab' => $tab,
            'search' => $validated['search'] ?? null,
        ])->with('success', "{$affected} {$entityLabel}(s) {$actionLabel} successfully.");
    }

    public function manageSellers(Request $request)
    {
        $this->ensureAdmin();
        $search = trim((string) $request->get('search', ''));
        $sellers = Seller::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('business_name', 'like', "%{$search}%");
                });
            })
            ->paginate(10)
            ->withQueryString();
        return view('admin.sellers', compact('sellers'));
    }

    public function manageProducts(Request $request)
    {
        $this->ensureAdmin();
        $search = trim((string) $request->get('search', ''));
        $products = Product::query()
            ->with('seller')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                            $sellerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->paginate(10)
            ->withQueryString();
        return view('admin.products', compact('products'));
    }

    public function settings()
    {
        $this->ensureAdmin();
        $promoCodes = PromoCode::latest()->paginate(10);
        return view('admin.settings', compact('promoCodes'));
    }

    public function storePromoCode(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0.01|max:999999',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'maximum_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['minimum_order_amount'] = $validated['minimum_order_amount'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        PromoCode::create($validated);

        return back()->with('success', 'Promo code created.');
    }

    public function rejectSeller($id)
    {
        $this->ensureAdmin();
        $seller = Seller::find($id);
        $seller->is_deleted = 1;
        $seller->save();

        return redirect()->route('admin.dashboard')->with('status', 'Seller rejected and deleted.');
    }

    public function loginAsSeller($id)
    {
        $this->ensureAdmin();
        $admin = Auth::user();
        $seller = Seller::find($id);

        if ($seller) {
            $issuedAt = now()->timestamp;
            $nonce = Str::random(40);
            $payload = [
                'admin_id' => $admin->id,
                'seller_id' => $seller->id,
                'issued_at' => $issuedAt,
                'nonce' => $nonce,
                'signature' => hash_hmac('sha256', "{$admin->id}|{$seller->id}|{$issuedAt}|{$nonce}", (string) config('app.key')),
            ];
            session(['admin_impersonation' => $payload]);

            Auth::guard('seller')->login($seller);
            return redirect()->route('seller.panel');
        }
        return redirect()->back()->with('error', 'Seller not found');
    }

    public function returnToAdmin()
    {
        $payload = session('admin_impersonation');
        if (!is_array($payload)) {
            return redirect()->route('login')->with('error', 'Impersonation session is missing.');
        }

        $adminId = (int) ($payload['admin_id'] ?? 0);
        $sellerId = (int) ($payload['seller_id'] ?? 0);
        $issuedAt = (int) ($payload['issued_at'] ?? 0);
        $nonce = (string) ($payload['nonce'] ?? '');
        $signature = (string) ($payload['signature'] ?? '');
        $expectedSignature = hash_hmac('sha256', "{$adminId}|{$sellerId}|{$issuedAt}|{$nonce}", (string) config('app.key'));

        if (
            !$adminId ||
            !$sellerId ||
            !$issuedAt ||
            empty($nonce) ||
            empty($signature) ||
            !hash_equals($expectedSignature, $signature) ||
            now()->timestamp > ($issuedAt + 900) ||
            (int) Auth::guard('seller')->id() !== $sellerId
        ) {
            session()->forget('admin_impersonation');
            Auth::guard('seller')->logout();
            return redirect()->route('login')->with('error', 'Impersonation session is invalid or expired.');
        }

        $admin = User::find($adminId);
        if (!$admin || (int) ($admin->sellerType ?? 0) !== 1) {
            session()->forget('admin_impersonation');
            Auth::guard('seller')->logout();
            return redirect()->route('login')->with('error', 'Original admin session is invalid.');
        }

        Auth::guard('seller')->logout();

        Auth::loginUsingId($adminId);
        session()->forget('admin_impersonation');

        return redirect()->route('admin.dashboard');
    }
}
