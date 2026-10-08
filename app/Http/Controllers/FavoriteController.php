<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Seller;
use App\Models\ShopProduct;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggleSeller(Seller $seller)
    {
        $userId = auth()->id();
        $favorite = Favorite::where('user_id', $userId)->where('seller_id', $seller->id)->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Store removed from favorites.');
        }

        Favorite::create(['user_id' => $userId, 'seller_id' => $seller->id]);
        return back()->with('success', 'Store added to favorites.');
    }

    public function toggleProduct(ShopProduct $listing)
    {
        $userId = auth()->id();
        $favorite = Favorite::where('user_id', $userId)->where('shop_product_id', $listing->id)->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Product removed from favorites.');
        }

        Favorite::create(['user_id' => $userId, 'shop_product_id' => $listing->id]);
        return back()->with('success', 'Product added to favorites.');
    }
}
