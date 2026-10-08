<?php
namespace App\Http\Controllers;

use App\Models\User as AppUser;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Notifications\NewServiceAddedNotification; // Use Generic notification or rename later
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreProductRequest;
use App\Models\Seller;

class SellerProductController extends Controller
{
    public function showAddProductForm()
    {
        $seller = auth()->guard('seller')->user();
        return view('seller.add-product', compact('seller'));
    }

    public function storeProduct(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $imagePath = $request->file('image')->store('products', 'public');

        $product = Product::create(array_merge($validated, [
            'seller_id' => auth()->guard('seller')->id(),
            'image' => $imagePath,
            'is_approved' => false,
        ]));

        $sellerName = auth()->guard('seller')->user()->name;
        $sellerId = auth()->guard('seller')->user()->id;
        $productId = $product->id;

        $users = AppUser::latest()->take(5)->get();

        foreach ($users as $user) {
            try {
                $user->notify(new NewServiceAddedNotification($sellerName, $product->name, $sellerId, $productId));
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('seller.panel')->with('success', 'Product added successfully and is awaiting admin approval.');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        if ($product->seller_id != auth()->guard('seller')->id()) {
            abort(403);
        }

        return view('seller.edit-product', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->seller_id != auth()->guard('seller')->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'seller_city' => 'required|string|max:255',
            'seller_area' => 'required|string|max:255',
            'seller_contact_no' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'unit_type' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::delete('public/' . $product->image);
            }

            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('seller.panel')->with('success', 'Product updated successfully');
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        if ($product->seller_id != auth()->guard('seller')->id()) {
            abort(403);
        }

        $product->delete();

        return redirect()->route('seller.panel')->with('success', 'Product deleted successfully!');
    }
}
