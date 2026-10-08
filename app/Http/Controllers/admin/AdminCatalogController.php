<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogCategory;
use App\Models\GlobalProduct;
use App\Models\ShopProduct;
use App\Jobs\SyncProductPriceUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCatalogController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->integer('category_id');

        $categories = CatalogCategory::orderBy('name')->get();
        $activeCategory = $categoryId
            ? $categories->firstWhere('id', $categoryId)
            : $categories->first();

        $products = collect();
        if ($activeCategory) {
            $products = GlobalProduct::where('catalog_category_id', $activeCategory->id)
                ->orderBy('name')
                ->get();
        }

        return view('admin.catalog.index', compact('categories', 'activeCategory', 'products'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:catalog_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        CatalogCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'created_by_admin_id' => Auth::id(),
            'updated_by_admin_id' => Auth::id(),
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, CatalogCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:catalog_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'updated_by_admin_id' => Auth::id(),
        ]);

        return back()->with('success', 'Category updated successfully.');
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'catalog_category_id' => 'required|exists:catalog_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'base_price' => 'required|numeric|min:0',
            'unit_type' => 'required|string|max:50',
            'default_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('default_image')) {
            $imagePath = $request->file('default_image')->store('global-products', 'public');
        }

        GlobalProduct::create([
            'catalog_category_id' => $validated['catalog_category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'unit_type' => $validated['unit_type'],
            'default_image' => $imagePath,
            'is_active' => true,
            'created_by_admin_id' => Auth::id(),
            'updated_by_admin_id' => Auth::id(),
        ]);

        return back()->with('success', 'Master product added successfully.');
    }

    public function updateProduct(Request $request, GlobalProduct $product)
    {
        $validated = $request->validate([
            'catalog_category_id' => 'required|exists:catalog_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'base_price' => 'required|numeric|min:0',
            'unit_type' => 'required|string|max:50',
            'default_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('default_image')) {
            if ($product->default_image) {
                Storage::disk('public')->delete($product->default_image);
            }
            $product->default_image = $request->file('default_image')->store('global-products', 'public');
        }

        $product->catalog_category_id = $validated['catalog_category_id'];
        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? null;
        $product->base_price = $validated['base_price'];
        $product->unit_type = $validated['unit_type'];
        $product->is_active = $request->boolean('is_active');
        $product->updated_by_admin_id = Auth::id();
        $product->save();

        if ($request->boolean('push_to_non_custom_prices')) {
            SyncProductPriceUpdate::dispatch($product->id);
        }

        return back()->with('success', 'Master product updated successfully.');
    }

    public function destroyProduct(GlobalProduct $product)
    {
        $product->delete();
        return back()->with('success', 'Master product deleted successfully.');
    }
}
