<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SiteSettingsController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('site_settings')) {
            $settings = new Collection();
            return view('admin.settings.index', compact('settings'))
                ->with('error', 'Site settings table is missing. Run migrations to restore it.');
        }

        $settings = SiteSetting::orderBy('key')->get();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request, SiteSetting $setting)
    {
        if (!Schema::hasTable('site_settings')) {
            return back()->with('error', 'Site settings table is missing. Run migrations to restore it.');
        }

        $setting->update([
            'value' => !$setting->value,
        ]);

        return back()->with('success', 'Setting updated successfully.');
    }
}
