<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function index()
    {
        $flags = FeatureFlag::orderBy('group')->orderBy('key')->get();

        return view('admin.feature-flags.index', compact('flags'));
    }

    public function update(Request $request, FeatureFlag $featureFlag): RedirectResponse
    {
        $request->validate([
            'value' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $before = $featureFlag->toArray();
        $featureFlag->update([
            'value' => $request->input('value'),
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => auth()->id(),
        ]);
        Audit::log('update_feature_flag', 'feature_flags', $featureFlag->id, $before, $featureFlag->fresh()->toArray());

        return back()->with('success', 'Setting updated successfully.');
    }
}
