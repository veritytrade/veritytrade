<?php

namespace App\Modules\Phones\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Phones\Models\PhonePricingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhonePricingSettingsController extends Controller
{
    public function index(): View
    {
        $settings = PhonePricingSetting::orderByDesc('id')->get();

        return view('admin.phones.pricing-settings.index', compact('settings'));
    }

    public function create(): View
    {
        return view('admin.phones.pricing-settings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'exchange_rate' => 'required|numeric|min:0.01',
            'logistics_cny' => 'nullable|numeric|min:0',
            'rounding_unit' => 'required|integer|min:1',
            'profit_margin_ngn' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['logistics_cny'] = $request->input('logistics_cny', 0);
        $data['profit_margin_ngn'] = $request->input('profit_margin_ngn', 0);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_active']) {
            PhonePricingSetting::where('is_active', true)->update(['is_active' => false]);
        }

        PhonePricingSetting::create($data);

        return redirect()->route('admin.phones.pricing-settings.index')
            ->with('success', 'Pricing setting created.');
    }

    public function edit(PhonePricingSetting $setting): View
    {
        return view('admin.phones.pricing-settings.edit', compact('setting'));
    }

    public function update(Request $request, PhonePricingSetting $setting): RedirectResponse
    {
        $data = $request->validate([
            'exchange_rate' => 'required|numeric|min:0.01',
            'logistics_cny' => 'nullable|numeric|min:0',
            'rounding_unit' => 'required|integer|min:1',
            'profit_margin_ngn' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['logistics_cny'] = $request->input('logistics_cny', 0);
        $data['profit_margin_ngn'] = $request->input('profit_margin_ngn', 0);
        $data['is_active'] = $request->boolean('is_active', false);

        if ($data['is_active']) {
            PhonePricingSetting::where('is_active', true)->where('id', '!=', $setting->id)->update(['is_active' => false]);
        }

        $setting->update($data);

        return redirect()->route('admin.phones.pricing-settings.index')
            ->with('success', 'Pricing setting updated.');
    }

    public function destroy(PhonePricingSetting $setting): RedirectResponse
    {
        $setting->delete();

        return redirect()->route('admin.phones.pricing-settings.index')
            ->with('success', 'Pricing setting deleted.');
    }
}
