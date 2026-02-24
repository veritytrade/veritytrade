<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Brand Controller
|--------------------------------------------------------------------------
| Handles CRUD for Brands.
| Includes pricing engine toggle per brand.
|--------------------------------------------------------------------------
*/

class BrandController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Display Brand List
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Brand::with('category')->orderBy('id', 'desc');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $brands = $query->get();

        return view('admin.brands.index', compact('brands', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store New Brand
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands')
                    ->where(function ($query) use ($request) {
                        return $query->where('category_id', $request->category_id)
                                     ->whereNull('deleted_at');
                    }),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $request->file('image')?->store('brands', 'public');

        Brand::create([
            'uuid' => (string) Str::uuid(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'representative_image' => $imagePath,
            'image_path' => $imagePath,
            'is_active' => true,
            'position' => 0,
            'uses_pricing_engine' => $request->has('uses_pricing_engine'),
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Brand Page
    |--------------------------------------------------------------------------
    */
    public function edit(Brand $brand)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.brands.edit', compact('brand', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Brand
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands')
                    ->where(function ($query) use ($request) {
                        return $query->where('category_id', $request->category_id)
                                     ->whereNull('deleted_at');
                    })
                    ->ignore($brand->id),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $brand->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('brands', 'public');
        }

        $brand->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'representative_image' => $imagePath,
            'image_path' => $imagePath,
            'uses_pricing_engine' => $request->has('uses_pricing_engine'),
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Soft Delete Brand
    |--------------------------------------------------------------------------
    */
    public function destroy(Brand $brand)
    {
        if ($brand->image_path && Storage::disk('public')->exists($brand->image_path)) {
            Storage::disk('public')->delete($brand->image_path);
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Active Status
    |--------------------------------------------------------------------------
    */
    public function toggle(Brand $brand)
    {
        $brand->is_active = !$brand->is_active;
        $brand->save();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Status updated.');
    }
}
