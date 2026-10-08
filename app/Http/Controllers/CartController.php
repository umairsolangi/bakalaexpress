<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function add(Request $request)
    {
        if ($request->filled('shop_product_id')) {
            return $this->addCatalogProduct($request);
        }

        return $this->addLegacyProduct($request);
    }

    public function viewCart()
    {
        [$cart, $cartMessages] = $this->refreshCart();

        return view('cart.view', compact('cart'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        [$cart] = $this->refreshCart();
        $cartKey = $validated['cart_key'];

        if (!isset($cart[$cartKey])) {
            return redirect()->route('cart.view')->with('error', 'That cart item is no longer available.');
        }

        $quantity = (int) $validated['quantity'];

        if (!empty($cart[$cartKey]['shop_product_id'])) {
            $listing = ShopProduct::where('is_active', true)->find($cart[$cartKey]['shop_product_id']);
            if (!$listing || (int) $listing->stock_quantity < $quantity) {
                return redirect()->route('cart.view')->with('error', 'Requested quantity exceeds available stock.');
            }
        } elseif (!empty($cart[$cartKey]['product_id'])) {
            $product = Product::find($cart[$cartKey]['product_id']);
            if ($product && $product->stock_quantity !== null && (int) $product->stock_quantity < $quantity) {
                return redirect()->route('cart.view')->with('error', 'Requested quantity exceeds available stock.');
            }
        }

        $cart[$cartKey]['quantity'] = $quantity;
        Session::put('cart', $cart);

        return redirect()->route('cart.view')->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $cart = Session::get('cart', []);
        $cartKey = $request->input('shop_product_id', $request->input('product_id'));

        if ($cartKey !== null) {
            unset($cart[$cartKey]);
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Product removed from cart!');
    }

    private function addCatalogProduct(Request $request)
    {
        $request->validate([
            'shop_product_id' => 'required|exists:shop_product,id',
        ]);

        $listing = ShopProduct::with(['seller', 'globalProduct'])
            ->where('is_active', true)
            ->findOrFail($request->shop_product_id);

        if (!$listing->globalProduct || !$listing->globalProduct->is_active) {
            return redirect()->back()->with('error', 'This item is no longer available.');
        }

        if (!$listing->seller?->isAcceptingOrders()) {
            return redirect()->back()->with('error', 'This seller is currently closed.');
        }

        if ((int) $listing->stock_quantity < 1) {
            return redirect()->back()->with('error', 'This item is currently out of stock.');
        }

        $cart = Session::get('cart', []);

        if (!empty($cart)) {
            $existingSellerId = collect($cart)->pluck('seller_id')->filter()->first();
            if ($existingSellerId && (int) $existingSellerId !== (int) $listing->seller_id) {
                return redirect()->back()->with('error', 'You can only add products from one seller at a time. Clear your cart to switch sellers.');
            }
        }

        $cartKey = (string) $listing->id;

        if (isset($cart[$cartKey])) {
            if ((int) $listing->stock_quantity <= (int) $cart[$cartKey]['quantity']) {
                return redirect()->back()->with('error', 'You cannot add more than the seller has in stock.');
            }

            $cart[$cartKey]['quantity'] += 1;
        } else {
            $cart[$cartKey] = [
                'id' => $listing->id,
                'shop_product_id' => $listing->id,
                'global_product_id' => $listing->global_product_id,
                'seller_id' => $listing->seller_id,
                'seller_name' => $listing->seller?->name,
                'name' => $listing->globalProduct->name,
                'price' => $listing->effective_price,
                'base_price' => (float) $listing->globalProduct->base_price,
                'custom_price' => $listing->custom_price !== null ? (float) $listing->custom_price : null,
                'unit_type' => $listing->globalProduct->unit_type,
                'image' => $listing->globalProduct->default_image,
                'quantity' => 1,
            ];
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    private function addLegacyProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::with('seller')->findOrFail($request->product_id);
        if (!$product->seller?->isAcceptingOrders()) {
            return redirect()->back()->with('error', 'This seller is currently closed.');
        }
        $cart = Session::get('cart', []);

        if (!empty($cart)) {
            $existingSellerId = collect($cart)->pluck('seller_id')->filter()->first();
            if ($existingSellerId && (int) $existingSellerId !== (int) $product->seller_id) {
                return redirect()->back()->with('error', 'You can only add products from one seller at a time. Clear your cart to switch sellers.');
            }
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += 1;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => 1,
                'seller_id' => $product->seller_id,
            ];
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    private function refreshCart(): array
    {
        $cart = Session::get('cart', []);
        $messages = [];

        // Collect all IDs to bulk-query
        $shopProductIds = [];
        $productIds = [];
        foreach ($cart as $item) {
            if (!empty($item['shop_product_id'])) {
                $shopProductIds[] = $item['shop_product_id'];
            } elseif (!empty($item['product_id'])) {
                $productIds[] = $item['product_id'];
            } elseif (!empty($item['id'])) {
                $productIds[] = $item['id'];
            }
        }

        // Bulk fetch ShopProduct listings
        $shopProducts = empty($shopProductIds)
            ? collect()
            : ShopProduct::with(['seller', 'globalProduct'])
                ->where('is_active', true)
                ->whereIn('id', array_unique($shopProductIds))
                ->get()
                ->keyBy('id');

        // Bulk fetch legacy Products
        $legacyProducts = empty($productIds)
            ? collect()
            : Product::with('seller')
                ->whereIn('id', array_unique($productIds))
                ->get()
                ->keyBy('id');

        foreach ($cart as $key => $item) {
            if (!empty($item['shop_product_id'])) {
                $listing = $shopProducts->get($item['shop_product_id']);

                if (!$listing || !$listing->globalProduct || !$listing->globalProduct->is_active) {
                    unset($cart[$key]);
                    $messages[] = ($item['name'] ?? 'An item') . ' was removed because it is no longer available.';
                    continue;
                }

                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $stockQuantity = (int) $listing->stock_quantity;

                if ($stockQuantity < $quantity) {
                    $quantity = max(1, $stockQuantity);
                    $messages[] = $listing->globalProduct->name . ' quantity was adjusted to current stock.';
                }

                if ($stockQuantity < 1) {
                    $cart[$key]['stock_warning'] = 'Out of stock. Remove this item before checkout.';
                } elseif ($stockQuantity <= 5) {
                    $cart[$key]['stock_warning'] = 'Only ' . $stockQuantity . ' left.';
                } else {
                    unset($cart[$key]['stock_warning']);
                }

                $cart[$key] = array_merge($cart[$key], [
                    'seller_id' => $listing->seller_id,
                    'seller_name' => $listing->seller?->name,
                    'name' => $listing->globalProduct->name,
                    'price' => $listing->effective_price,
                    'unit_type' => $listing->globalProduct->unit_type,
                    'image' => $listing->globalProduct->default_image,
                    'quantity' => $quantity,
                    'stock_quantity' => $stockQuantity,
                ]);

                continue;
            }

            $prodId = $item['product_id'] ?? $item['id'] ?? null;
            $product = $prodId ? $legacyProducts->get($prodId) : null;

            if (!$product) {
                unset($cart[$key]);
                $messages[] = ($item['name'] ?? 'An item') . ' was removed because it is no longer available.';
                continue;
            }

            $stockQuantity = (int) ($product->stock_quantity ?? 0);
            if ($stockQuantity < 1) {
                $cart[$key]['stock_warning'] = 'Out of stock. Remove this item before checkout.';
            } elseif ($stockQuantity <= 5) {
                $cart[$key]['stock_warning'] = 'Only ' . $stockQuantity . ' left.';
            } else {
                unset($cart[$key]['stock_warning']);
            }

            $cart[$key] = array_merge($cart[$key], [
                'product_id' => $product->id,
                'seller_id' => $product->seller_id,
                'seller_name' => $product->seller?->name,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'stock_quantity' => $stockQuantity,
            ]);
        }

        Session::put('cart', $cart);

        foreach ($messages as $message) {
            session()->flash('error', trim((string) session('error') . ' ' . $message));
        }

        return [$cart, $messages];
    }
}
