<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SeriesController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Display Series List
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::with('category')->orderBy('name')->get();

        $query = Series::with('brand.category')
            ->orderBy('id', 'desc');

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        $series = $query->get();

        return view('admin.series.index', compact(
            'series',
            'brands',
            'categories'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Series
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('series')
                    ->where(function ($query) use ($request) {
                        return $query->where('brand_id', $request->brand_id)
                                     ->whereNull('deleted_at');
                    }),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $request->file('image')?->store('series', 'public');

        Series::create([
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'representative_image' => $imagePath,
            'image_path' => $imagePath,
            'is_active' => true,
            'position' => 0,
        ]);

        return redirect()->route('admin.series.index')
            ->with('success', 'Series created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
    public function edit(Series $series)
    {
        $brands = Brand::with('category')->orderBy('name')->get();

        return view('admin.series.edit', compact('series', 'brands'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Series $series)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('series')
                    ->where(function ($query) use ($request) {
                        return $query->where('brand_id', $request->brand_id)
                                     ->whereNull('deleted_at');
                    })
                    ->ignore($series->id),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $series->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('series', 'public');
        }

        $series->update([
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'representative_image' => $imagePath,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.series.index')
            ->with('success', 'Series updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(Series $series)
    {
        if ($series->image_path && Storage::disk('public')->exists($series->image_path)) {
            Storage::disk('public')->delete($series->image_path);
        }

        $series->delete();

        return redirect()->route('admin.series.index')
            ->with('success', 'Series deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle
    |--------------------------------------------------------------------------
    */
    public function toggle(Series $series)
    {
        $series->is_active = !$series->is_active;
        $series->save();

        return redirect()->route('admin.series.index')
            ->with('success', 'Status updated.');
    }
}
