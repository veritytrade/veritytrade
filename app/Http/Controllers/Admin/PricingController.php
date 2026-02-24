<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Series;
use App\Models\Device;  // ✅ Using Device instead of Model
use App\Models\Memory;
use App\Models\FunctionalityGrade;
use App\Models\AppearanceGrade;
use App\Models\PriceRule;
use App\Support\Audit;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PRICING DASHBOARD: Show filters and price combinations
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        // Get brands that have pricing engine enabled
        $brands = Brand::where('uses_pricing_engine', true)
            ->where('is_active', true)
            ->get();

        // Initialize empty collections (will be filled based on filters)
        $series = collect();
        $models = collect();  // Variable name can stay "models" - it's just a label
        $priceRules = collect();

        // If user selected a brand, show its series
        if ($request->filled('brand')) {
            $series = Series::where('brand_id', $request->brand)->get();
        }

        // If user selected a series, show its devices (models)
        if ($request->filled('series')) {
            $models = Device::where('series_id', $request->series)->get(); // ✅ Device class
        }

        // If user selected a device, show its price combinations
        if ($request->filled('model')) {
            $priceRules = PriceRule::with([
                'memory',
                'functionalityGrade',
                'appearanceGrade'
            ])
            ->where('model_id', $request->model)
            ->get();
        }

        // Get all available options for the "Add Combination" form
        $memories = Memory::where('is_active', true)->get();
        $functionalities = FunctionalityGrade::where('is_active', true)->get();
        $appearances = AppearanceGrade::where('is_active', true)->get();

        return view('admin.pricing.index', compact(
            'brands',
            'series',
            'models',  // Passed to Blade as $models (variable name is fine)
            'priceRules',
            'memories',
            'functionalities',
            'appearances'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE PRICE RULE: Save new price combination
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:models,id',  // Table is still "models"
            'memory_id' => 'required|exists:memories,id',
            'functionality_grade_id' => 'required|exists:functionality_grades,id',
            'appearance_grade_id' => 'required|exists:appearance_grades,id',
            'min_price_cny' => 'required|numeric',
            'max_price_cny' => 'required|numeric|gte:min_price_cny',
        ]);

        PriceRule::create($request->all());
        Audit::log('create_price_rule', 'price_rules', null, null, $request->all());

        return back()->with('success', 'Price rule created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE ACTIVE: Enable/disable price combination
    |--------------------------------------------------------------------------
    */
    public function toggle(PriceRule $priceRule)
    {
        $before = $priceRule->toArray();
        $priceRule->is_active = !$priceRule->is_active;
        $priceRule->save();
        Audit::log('toggle_price_rule', 'price_rules', $priceRule->id, $before, $priceRule->fresh()->toArray());

        return back()->with('success', 'Status updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE: Remove price combination
    |--------------------------------------------------------------------------
    */
    public function destroy(PriceRule $priceRule)
    {
        $before = $priceRule->toArray();
        $priceRule->delete();
        Audit::log('delete_price_rule', 'price_rules', $priceRule->id, $before, null);

        return back()->with('success', 'Price rule deleted.');
    }
}
