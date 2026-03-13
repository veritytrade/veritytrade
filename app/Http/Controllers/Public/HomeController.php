<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Deal;
use App\Models\HomepageHero;
use App\Modules\Phones\Models\PhoneBrand;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $categories = collect();
        $deals = collect();
        $phoneBrands = collect();

        // Categories (optional): for extra tabs; do not block page.
        if (Schema::hasTable('categories')) {
            try {
                $categories = Category::where('is_active', true)
                    ->orderBy('position')
                    ->get();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Hot deals: show only active deals that have not expired.
        if (Schema::hasTable('deals')) {
            try {
                $deals = Deal::query()
                    ->with('images')
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->orderBy('position', 'desc')
                    ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('expires_at', 'desc')
                    ->get();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Phones module: show active brands for Phones tab.
        if (Schema::hasTable('phone_brands')) {
            try {
                $phoneBrands = PhoneBrand::where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $hero = null;
        if (Schema::hasTable('homepage_hero')) {
            try {
                $hero = HomepageHero::get();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('public.home', compact('categories', 'deals', 'phoneBrands', 'hero'));
    }
}
