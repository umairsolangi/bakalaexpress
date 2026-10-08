<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\Seller;
use App\Models\ShopProduct;

class ProductController extends Controller
{
    public function showProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $seller = $product->seller;
            return view('show', compact('product', 'seller'));
        } else {
            return redirect()->route('home')->with('error', 'Product not found.');
        }
    }

    public function showCatalogProduct($sellerId, $listingId)
    {
        $seller = Seller::findOrFail($sellerId);

        if (!$seller->isAcceptingOrders()) {
            $hours = date('h:i A', strtotime($seller->opens_at ?? '07:00')) . ' – ' . date('h:i A', strtotime($seller->closes_at ?? '23:00'));
            return redirect()->route('home')->with('store_closed_modal', [
                'name' => $seller->name,
                'hours' => $hours,
            ]);
        }

        $listing = ShopProduct::with(['seller', 'globalProduct'])
            ->where('seller_id', $seller->id)
            ->where('is_active', true)
            ->findOrFail($listingId);

        if (!$listing->globalProduct || !$listing->globalProduct->is_active) {
            return redirect()->route('sellers.services', $seller->id)->with('error', 'Product not found.');
        }

        $product = $listing->globalProduct;
        $product->setRelation('pivot', $listing);

        return view('show', compact('seller', 'product', 'listing'));
    }

    public function showSellerProducts($seller_id)
    {
        $seller = Seller::with('catalogCategory')->findOrFail($seller_id);

        if (!$seller->isAcceptingOrders()) {
            $hours = date('h:i A', strtotime($seller->opens_at ?? '07:00')) . ' – ' . date('h:i A', strtotime($seller->closes_at ?? '23:00'));
            return redirect()->route('home')->with('store_closed_modal', [
                'name' => $seller->name,
                'hours' => $hours,
            ]);
        }

        $products = $seller->catalogProducts()
            ->wherePivot('is_active', true)
            ->where('global_products.is_active', true)
            ->orderBy('name')
            ->get();

        $feedbacks = Feedback::where('seller_id', $seller_id)
            ->with(['user', 'seller', 'order'])
            ->whereHas('user')
            ->where('status', 'approved')
            ->get();

        return view('seller-products', compact('seller', 'products', 'feedbacks'));
    }

    public function searchProducts(Request $request)
    {
        $query = trim((string) $request->get('q'));

        if ($query === '') {
            return response()->json(['products' => []]);
        }

        $products = ShopProduct::with(['seller:id,name', 'globalProduct:id,name,description,base_price'])
            ->where('is_active', true)
            ->when($request->boolean('in_stock'), function ($builder) {
                $builder->where('stock_quantity', '>', 0);
            })
            ->when($request->filled('seller_id'), function ($builder) use ($request) {
                $builder->where('seller_id', $request->integer('seller_id'));
            })
            ->whereHas('globalProduct', function ($builder) use ($query) {
                $builder->where('is_active', true)
                    ->where(function ($queryBuilder) use ($query) {
                        $queryBuilder->where('name', 'like', '%' . $query . '%')
                            ->orWhere('description', 'like', '%' . $query . '%');
                    });
            })
            ->when($request->filled('category_id'), function ($builder) use ($request) {
                $builder->whereHas('globalProduct', function ($productQuery) use ($request) {
                    $productQuery->where('catalog_category_id', $request->integer('category_id'));
                });
            })
            ->limit(10)
            ->get()
            ->map(function (ShopProduct $listing) {
                return [
                    'shop_product_id' => $listing->id,
                    'seller_id' => $listing->seller_id,
                    'name' => $listing->globalProduct?->name,
                    'description' => $listing->globalProduct?->description,
                    'seller_name' => $listing->seller?->name,
                    'price' => $listing->effective_price,
                    'stock_quantity' => $listing->stock_quantity,
                    'is_available' => (int) $listing->stock_quantity > 0,
                ];
            })
            ->values();

        return response()->json(['products' => $products]);
    }
}
