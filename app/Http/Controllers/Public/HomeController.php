<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Deal;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $categories = collect();
        $deals = collect();
        $phoneBrands = collect();

        if (!Schema::hasTable('categories')) {
            return view('public.home', compact('categories', 'deals', 'phoneBrands'));
        }

        // Dynamic categories for tabs (only active ones).
        // Category tabs should not depend on other modules being present.
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('position')
                ->get();
        } catch (\Throwable $e) {
            report($e);
            $categories = collect();
        }

        // Hot deals are optional and should never block category tabs.
        if (Schema::hasTable('deals')) {
            try {
                $deals = Deal::available()
                    ->with('images')
                    ->orderBy('position', 'desc')
                    ->orderBy('expires_at', 'desc')
                    ->get();
            } catch (\Throwable $e) {
                report($e);
                $deals = collect();
            }
        }

        // Phones module (category name can evolve; detect by name containing "phone").
        if (Schema::hasTable('brands')) {
            $phoneCategory = $categories->first(function ($category) {
                return Str::contains(Str::lower((string) $category->name), 'phone');
            });

            try {
                $phoneBrands = $phoneCategory
                    ? Brand::where('category_id', $phoneCategory->id)
                        ->where('is_active', true)
                        ->where('uses_pricing_engine', true)
                        ->orderBy('position')
                        ->get()
                    : collect();
            } catch (\Throwable $e) {
                report($e);
                $phoneBrands = collect();
            }
        }

        return view('public.home', compact('categories', 'deals', 'phoneBrands'));
    }
}
