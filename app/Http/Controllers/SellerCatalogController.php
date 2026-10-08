<?php

namespace App\Http\Controllers;

use App\Models\GlobalProduct;
use App\Models\Seller;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerCatalogController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $seller->load('catalogCategory');

        $search = $request->query('search');

        $listings = ShopProduct::with('globalProduct')
            ->where('seller_id', $seller->id)
            ->whereHas('globalProduct', function ($query) use ($search) {
                $query->where('is_active', true);
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->orderByDesc('is_active')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $availableInCategoryCount = 0;
        if ($seller->catalog_category_id) {
            $availableInCategoryCount = GlobalProduct::where('catalog_category_id', $seller->catalog_category_id)
                ->where('is_active', true)
                ->count();
        }

        return view('seller.catalog.index', compact('seller', 'listings', 'availableInCategoryCount'));
    }

    public function importCategoryCatalog()
    {
        /** @var Seller $seller */
        $seller = Auth::guard('seller')->user();

        if (!$seller->catalog_category_id) {
            return back()->with('error', 'Please assign a shop category first.');
        }

        $productIds = GlobalProduct::where('catalog_category_id', $seller->catalog_category_id)
            ->where('is_active', true)
            ->pluck('id');

        if ($productIds->isEmpty()) {
            return back()->with('error', 'No active master products found for your category.');
        }

        $attachData = $productIds->mapWithKeys(function ($id) {
            return [
                $id => [
                    'custom_price' => null,
                    'stock_quantity' => 0,
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];
        })->all();

        $beforeCount = ShopProduct::where('seller_id', $seller->id)->count();
        $seller->catalogProducts()->syncWithoutDetaching($attachData);
        $afterCount = ShopProduct::where('seller_id', $seller->id)->count();
        $imported = max(0, $afterCount - $beforeCount);

        return back()->with('success', $imported . ' products imported from master catalog.');
    }

    public function updateListing(Request $request, ShopProduct $listing)
    {
        $sellerId = Auth::guard('seller')->id();
        if ((int) $listing->seller_id !== (int) $sellerId) {
            abort(403);
        }

        $validated = $request->validate([
            'custom_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0|max:999999',
            'is_active' => 'nullable|boolean',
        ]);

        $listing->custom_price = $validated['custom_price'] ?? null;
        $listing->stock_quantity = $validated['stock_quantity'];
        $listing->is_active = $request->boolean('is_active');
        $listing->save();

        return back()->with('success', 'Listing updated successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        /** @var Seller $seller */
        $seller = Auth::guard('seller')->user();

        $validated = $request->validate([
            'bulk_action' => 'required|in:activate_all,deactivate_all,increase_percent,decrease_percent,reset_to_base_price',
            'percent_value' => 'nullable|numeric|min:0|max:500',
            'listing_ids' => 'nullable|array',
            'listing_ids.*' => 'exists:shop_product,id',
        ]);

        $query = ShopProduct::where('seller_id', $seller->id)
            ->whereHas('globalProduct', function ($builder) {
                $builder->where('is_active', true);
            });

        if (!empty($validated['listing_ids'])) {
            $query->whereIn('id', $validated['listing_ids']);
        }

        if ($validated['bulk_action'] === 'activate_all') {
            $updated = $query->update(['is_active' => true]);
            return back()->with('success', $updated . ' listings activated.');
        }

        if ($validated['bulk_action'] === 'deactivate_all') {
            $updated = $query->update(['is_active' => false]);
            return back()->with('success', $updated . ' listings deactivated.');
        }

        if ($validated['bulk_action'] === 'reset_to_base_price') {
            $updated = $query->update(['custom_price' => null]);
            return back()->with('success', $updated . ' listings reset to base price.');
        }

        $percent = (float) ($validated['percent_value'] ?? 0);
        if ($percent <= 0) {
            return back()->with('error', 'Please enter a valid percent value.');
        }

        $factor = (float) ($validated['bulk_action'] === 'increase_percent' ? (1 + $percent / 100) : (1 - $percent / 100));

        $updatedCount = \Illuminate\Support\Facades\DB::table('shop_product')
            ->join('global_products', 'shop_product.global_product_id', '=', 'global_products.id')
            ->where('shop_product.seller_id', $seller->id)
            ->where('global_products.is_active', true)
            ->where(function ($builder) {
                $builder->where('shop_product.custom_price', '>', 0)
                    ->orWhere(function ($inner) {
                        $inner->whereNull('shop_product.custom_price')
                            ->where('global_products.base_price', '>', 0);
                    });
            })
            ->when(!empty($validated['listing_ids']), function ($builder) use ($validated) {
                $builder->whereIn('shop_product.id', $validated['listing_ids']);
            })
            ->update([
                'shop_product.custom_price' => \Illuminate\Support\Facades\DB::raw("ROUND(COALESCE(shop_product.custom_price, global_products.base_price) * {$factor}, 2)")
            ]);

        return back()->with('success', $updatedCount . ' listings updated by ' . $percent . '%.');
    }
}
