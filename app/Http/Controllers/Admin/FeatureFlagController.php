<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    private const VISIBLE_KEYS = [
        'require_email_verification',
        'require_admin_approval',
        'enable_customer_address',
        'mail_from_address',
        'mail_from_name',
        'whatsapp_number',
    ];

    private function normalizeFlagValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));
        $truthy = ['1', 'true', 'yes', 'on'];
        $falsy = ['0', 'false', 'no', 'off'];

        if (in_array($normalized, $truthy, true)) {
            return '1';
        }

        if (in_array($normalized, $falsy, true)) {
            return '0';
        }

        return $value;
    }

    public function index()
    {
        $flags = FeatureFlag::whereIn('key', self::VISIBLE_KEYS)
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return view('admin.feature-flags.index', compact('flags'));
    }

    public function update(Request $request, FeatureFlag $featureFlag): RedirectResponse
    {
        if (!in_array($featureFlag->key, self::VISIBLE_KEYS, true)) {
            abort(403, 'This setting is not editable in this panel.');
        }

        $request->validate([
            'value' => 'nullable|string|max:1000',
        ]);

        $before = $featureFlag->toArray();
        $featureFlag->update([
            'value' => $this->normalizeFlagValue($request->input('value')),
            'updated_by' => auth()->id(),
        ]);
        Audit::log('update_feature_flag', 'feature_flags', $featureFlag->id, $before, $featureFlag->fresh()->toArray());

        return back()->with('success', 'Setting updated successfully.');
    }
}
