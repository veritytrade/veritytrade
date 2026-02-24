<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Device;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModelController extends Controller
{
    public function index($seriesId)
    {
        $series = Series::with('brand.category')->findOrFail($seriesId);

        $models = Device::where('series_id', $seriesId)
            ->orderBy('position')
            ->get();

        return view('admin.models.index', compact('series', 'models'));
    }

    public function brandIndex(Brand $brand)
    {
        $models = Device::where('brand_id', $brand->id)
            ->whereNull('series_id')
            ->orderBy('position')
            ->get();

        return view('admin.models.by-brand', compact('brand', 'models'));
    }

    public function store(Request $request, $seriesId)
    {
        $series = Series::findOrFail($seriesId);

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $existing = Device::withTrashed()
            ->where('series_id', $seriesId)
            ->where('name', $request->name)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                return back()->with('success', 'Model restored successfully.');
            }

            return back()->withErrors([
                'name' => 'Model already exists for this series.',
            ]);
        }

        $imagePath = $request->file('image')?->store('models', 'public');

        Device::create([
            'uuid' => (string) Str::uuid(),
            'series_id' => $seriesId,
            'brand_id' => $series->brand_id,
            'name' => $request->name,
            'representative_image' => $imagePath,
            'image_path' => $imagePath,
            'is_active' => true,
            'position' => 0,
        ]);

        return back()->with('success', 'Model created successfully.');
    }

    public function storeByBrand(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $existing = Device::withTrashed()
            ->where('brand_id', $brand->id)
            ->whereNull('series_id')
            ->where('name', $request->name)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                return back()->with('success', 'Model restored successfully.');
            }

            return back()->withErrors([
                'name' => 'Model already exists for this brand.',
            ]);
        }

        $imagePath = $request->file('image')?->store('models', 'public');

        Device::create([
            'uuid' => (string) Str::uuid(),
            'series_id' => null,
            'brand_id' => $brand->id,
            'name' => $request->name,
            'representative_image' => $imagePath,
            'image_path' => $imagePath,
            'is_active' => true,
            'position' => 0,
        ]);

        return back()->with('success', 'Model created successfully.');
    }

    public function toggle(Device $device)
    {
        $device->is_active = !$device->is_active;
        $device->save();

        return back()->with('success', 'Status updated.');
    }

    public function destroy(Device $device)
    {
        $imagePath = $device->representative_image ?: $device->image_path;
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $device->delete();

        return back()->with('success', 'Model deleted.');
    }
}
