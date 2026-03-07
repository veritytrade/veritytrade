<?php

namespace App\Modules\Phones\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Phones\Models\PhoneBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PhoneBrandController extends Controller
{
    public function index(): View
    {
        $brands = PhoneBrand::orderBy('name')->get();

        return view('admin.phones.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.phones.brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('phones/brands', 'public');
        } else {
            $data['image'] = null;
        }

        PhoneBrand::create($data);

        return redirect()->route('admin.phones.brands.index')
            ->with('success', 'Brand created.');
    }

    public function edit(PhoneBrand $brand): View
    {
        return view('admin.phones.brands.edit', compact('brand'));
    }

    public function update(Request $request, PhoneBrand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }
            $data['image'] = $request->file('image')->store('phones/brands', 'public');
        }

        $brand->update($data);

        return redirect()->route('admin.phones.brands.index')
            ->with('success', 'Brand updated.');
    }

    public function destroy(PhoneBrand $brand): RedirectResponse
    {
        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }
        $brand->delete();

        return redirect()->route('admin.phones.brands.index')
            ->with('success', 'Brand deleted.');
    }
}
