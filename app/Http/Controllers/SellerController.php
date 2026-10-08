<?php
namespace App\Http\Controllers;

use App\Models\CatalogCategory;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use App\Models\Service;
use App\Support\SanitizedImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Feedback;
use Illuminate\Support\Facades\Hash;


class SellerController extends Controller
{
    public function showRegisterForm()
    {
        $catalogCategories = CatalogCategory::where('is_active', true)->orderBy('name')->get();

        return view('auth.register-seller', compact('catalogCategories'));
    }
    public function register(Request $request)
    {
        // Custom validation messages
        $customMessages = [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please use a different email.',
            'email.max' => 'Email cannot exceed 255 characters.',
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,dns|max:255|unique:sellers',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'sector' => 'nullable|string|in:4A,4B,4C',
            'catalog_category_id' => 'nullable|exists:catalog_categories,id',
            'near_areas' => 'nullable|array|min:1',
            'near_areas.*' => 'string|max:100',
            'full_address' => 'nullable|string|min:10|max:500',
            'terms' => 'required',
        ], $customMessages);

        try {
            // Additional custom email validation
            $email = $request->email;
            $errors = [];

            // Check for suspicious domains
            $suspiciousDomains = ['example.com', 'test.com', 'sample.com', 'temp-mail.org', 'fakeemail.com'];
            foreach ($suspiciousDomains as $domain) {
                if (strpos($email, $domain) !== false) {
                    $errors['email'] = 'Please use a real email address, not a temporary or test email.';
                    break;
                }
            }

            // Check for valid email format beyond Laravel's validation
            $parts = explode('@', $email);
            if (count($parts) == 2) {
                $domain = $parts[1];
                // Check domain has at least one dot
                if (!str_contains($domain, '.') || substr_count($domain, '.') > 3) {
                    $errors['email'] = 'Please provide an email with a valid domain format.';
                }
            }

            // Return with errors if any additional validations fail
            if (!empty($errors)) {
                return back()->withInput()->withErrors($errors);
            }

            if ($request->hasFile('profile_image')) {
                $imagePath = SanitizedImageUpload::storeProfileImage($request->file('profile_image'));
            } else {
                $imagePath = null;
            }

            $defaultCategoryId = $request->catalog_category_id
                ?: CatalogCategory::where('is_active', true)->orderBy('name')->value('id');

            $seller = Seller::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password, // Don't use bcrypt here - the model's mutator will handle it
                'profile_image' => $imagePath,
                'city' => $request->input('city', 'Karachi'),
                'area' => $request->input('area', 'Baldia Town'),
                'sector' => $request->input('sector', '4A'),
                'catalog_category_id' => $defaultCategoryId,
                'near_areas' => array_values($request->near_areas ?? ['General Area']),
                'full_address' => $request->input('full_address', $request->input('area', 'Baldia Town') . ', Karachi'),
                'accountIsApproved' => 0,
            ]);

            // Return to registration page with success parameter
            return redirect()->route('register.seller', ['registration' => 'success']);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function showLoginForm()
    {
        return view('auth.login-seller');
    }

    public function login(Request $request)
    {
        // Custom validation messages for login
        $customMessages = [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ];

        $request->validate([
            'email' => 'required|string|email:rfc',
            'password' => 'required|string',
        ], $customMessages);

        // Additional email validation for common mistakes
        $email = $request->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->withInput()->withErrors(['email' => 'The email format is invalid. Please check for typos.']);
        }

        $seller = Seller::where('email', $request->email)->first();

        if (!$seller || !Hash::check($request->password, $seller->password)) {
            return back()->withInput()->withErrors(['email' => 'These credentials do not match our records.']);
        }

        if ($seller->accountIsApproved == 0) {
            return back()->withErrors(['email' => 'Your account is pending approval. Please wait for admin approval.']);
        }

        Auth::guard('seller')->login($seller, $request->filled('remember'));
        return redirect()->intended(route('seller.panel'));
    }


    public function showServices($seller_id)
    {
        $seller = Seller::findOrFail($seller_id);
        $services = Service::where('seller_id', $seller_id)->get();

        // The feedback table uses seller_id but references the users table
        // We need to check if there's a corresponding user with the same ID
        $feedbacks = Feedback::where('seller_id', $seller_id)
            ->with(['user', 'seller', 'order'])
            ->whereHas('user')
            ->where('status', 'approved')
            ->get();

        return view('seller-services', compact('seller', 'services', 'feedbacks'));
    }







    public function sellerPanel()
    {
        $seller = auth()->guard('seller')->user();

        $products = $seller->products()->latest()->paginate(12);
        $orders = Order::where('seller_id', $seller->id)
            ->with(['user', 'items.product'])
            ->latest()
            ->paginate(20);

        $ordersBaseQuery = Order::where('seller_id', $seller->id);

        $completedOrdersCount = (clone $ordersBaseQuery)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orWhere('status', 'delivered');
            })
            ->count();

        $pendingOrdersCount = (clone $ordersBaseQuery)->where('status', 'pending')->count();
        $processingOrdersCount = (clone $ordersBaseQuery)
            ->whereIn('status', ['confirmed_by_seller', 'processing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up'])
            ->count();
        $totalOrdersCount = (clone $ordersBaseQuery)->count();
        $totalProductsCount = $seller->products()->count();

        $quickActionOrders = Order::where('seller_id', $seller->id)
            ->whereIn('status', ['pending', 'confirmed_by_seller', 'processing', 'ready_for_pickup'])
            ->with(['user'])
            ->latest()
            ->take(5)
            ->get();

        $notifications = $seller->notifications()->latest()->take(20)->get();

        return view('seller.panel', compact(
            'products',
            'orders',
            'notifications',
            'completedOrdersCount',
            'pendingOrdersCount',
            'processingOrdersCount',
            'totalOrdersCount',
            'totalProductsCount',
            'quickActionOrders'
        ));
    }



    public function logout(Request $request)
    {
        Auth::guard('seller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login.seller');
    }

    /**
     * Show the seller's earnings dashboard
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function earnings(Request $request)
    {
        $seller = auth()->guard('seller')->user();
        if (!$seller) {
            return redirect()->route('login.seller');
        }

        $period = $request->get('period', 'all');

        // Get earnings for different time periods
        $allTimeEarnings = Order::getSellerEarnings($seller->id);
        $currentPeriodEarnings = Order::getSellerEarnings($seller->id, $period);

        // Get detailed data for completed orders
        $completedOrders = Order::where('seller_id', $seller->id)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orWhere('status', 'delivered');
            })
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get monthly earnings data for the chart
        $monthlyData = [];
        for ($i = 0; $i < 6; $i++) {
            $month = now()->subMonths($i);
            $earnings = Order::where('seller_id', $seller->id)
                ->where(function ($query) {
                    $query->where('status', 'completed')
                        ->orWhere('status', 'delivered');
                })
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount');

            $monthlyData[$month->format('M Y')] = $earnings;
        }
        // Reverse to show oldest to newest
        $monthlyData = array_reverse($monthlyData, true);

        return view('seller.earnings', [
            'seller' => $seller,
            'period' => $period,
            'allTimeEarnings' => $allTimeEarnings,
            'currentPeriodEarnings' => $currentPeriodEarnings,
            'completedOrders' => $completedOrders,
            'monthlyData' => $monthlyData
        ]);
    }

    public function updateOperatingHours(Request $request)
    {
        $seller = auth()->guard('seller')->user();

        $validated = $request->validate([
            'opens_at' => 'nullable|date_format:H:i',
            'closes_at' => 'nullable|date_format:H:i',
            'is_open' => 'nullable|boolean',
        ]);

        $seller->forceFill([
            'opens_at' => $validated['opens_at'] ?? null,
            'closes_at' => $validated['closes_at'] ?? null,
            'is_open' => $request->boolean('is_open'),
        ])->save();

        return back()->with('success', 'Operating hours updated.');
    }
}
