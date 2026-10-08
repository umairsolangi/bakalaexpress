<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Feedback;


class HomeController extends Controller
{
    public function showHomePage(Request $request)
    {
        if (auth()->guard('seller')->check()) {
            return redirect()->route('seller.panel');
        }

        if (auth()->guard('rider')->check()) {
            return redirect()->route('rider.dashboard');
        }

        if (auth()->guard('web')->check()) {
            $user = auth()->user();
            if ($user && (int) ($user->sellerType ?? 0) === 1) {
                return redirect()->route('admin.dashboard');
            }
        }


        $feedbacks = Feedback::with(['user', 'seller', 'order'])
            ->whereHas('user')
            ->whereHas('seller')
            ->where('status', 'approved')
            ->latest()
            ->get(); // Fetch only feedbacks with valid relationships
            
        $categories = \App\Models\CatalogCategory::where('is_active', true)->orderBy('name')->get();

        $sellersQuery = Seller::query()
            ->withCount(['feedbacks', 'catalogProducts'])
            ->withAvg('approvedFeedbacks', 'rating')
            ->where('is_deleted', false)
            ->where('accountIsApproved', 1)
            ->where('is_open', true);

        if ($request->filled('sector')) {
            $sellersQuery->where('sector', $request->sector);
        }

        if ($request->filled('near_area')) {
            $sellersQuery->whereJsonContains('near_areas', $request->near_area);
        }
        
        if ($request->filled('category')) {
            $sellersQuery->where('catalog_category_id', $request->category);
        }

        $sellers = $sellersQuery->paginate(20)->withQueryString();
        $sectorOptions = ['4A', '4B', '4C'];
        $nearAreaOptions = [
            'ABC Swimming Pool',
            'Tajli Noor Masjid',
            'Lahori Hotel',
            'Ali Chowk',
            'Family Park',
            'Carido Hospital',
            'Rubi Mor',
        ];
        // $notifications = auth()->user()->notifications()->latest()->take(5)->get();

        return view('home', compact('categories', 'sellers', 'feedbacks', 'sectorOptions', 'nearAreaOptions'));
    }


    public function index()
    {
        if (auth()->guard('seller')->check()) {
            return redirect()->route('seller.panel');
        }

        if (auth()->guard('rider')->check()) {
            return redirect()->route('rider.dashboard');
        }

        if (auth()->guard('web')->check()) {
            $user = auth()->user();
            if ($user && (int) ($user->sellerType ?? 0) === 1) {
                return redirect()->route('admin.dashboard');
            }
        }

        $sellers = Seller::where('is_deleted', false)->where('accountIsApproved', 1)->paginate(20)->withQueryString();
        return view('home', compact('sellers'));
    }
}
