<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\PricingSetting;
use App\Support\Audit;
use Illuminate\Http\Request;

class PricingSettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $brands = Brand::where('uses_pricing_engine', true)
            ->where('is_active', true)
            ->get();

        $settings = PricingSetting::with('brand')
            ->latest()
            ->get();

        return view('admin.pricing.settings', compact('brands', 'settings'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'exchange_rate' => 'required|numeric|min:1',
            'logistics_cost_cny' => 'required|numeric|min:0',
            'fixed_margin_ngn' => 'required|numeric|min:0',
            'price_rounding_unit' => 'nullable|integer|min:1',
        ]);

        // Deactivate existing active setting for this brand only
        PricingSetting::where('brand_id', $request->brand_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $created = PricingSetting::create([
            'brand_id' => $request->brand_id,
            'exchange_rate' => $request->exchange_rate,
            'logistics_cost_cny' => $request->logistics_cost_cny,
            'fixed_margin_ngn' => $request->fixed_margin_ngn,
            'price_rounding_unit' => $request->input('price_rounding_unit', 10000),
            'is_active' => true,
        ]);
        Audit::log('create_pricing_setting', 'pricing_settings', $created->id, null, $created->toArray());

        return back()->with('success', 'Pricing profile created and activated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle
    |--------------------------------------------------------------------------
    */
    public function toggle(PricingSetting $pricingSetting)
    {
        $before = $pricingSetting->toArray();
        // deactivate others for same brand
        PricingSetting::where('brand_id', $pricingSetting->brand_id)
            ->update(['is_active' => false]);

        $pricingSetting->update(['is_active' => true]);
        Audit::log('activate_pricing_setting', 'pricing_settings', $pricingSetting->id, $before, $pricingSetting->fresh()->toArray());

        return back()->with('success', 'Pricing profile activated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(PricingSetting $pricingSetting)
    {
        $before = $pricingSetting->toArray();
        $pricingSetting->delete();
        Audit::log('delete_pricing_setting', 'pricing_settings', $pricingSetting->id, $before, null);

        return back()->with('success', 'Pricing profile deleted.');
    }
}
