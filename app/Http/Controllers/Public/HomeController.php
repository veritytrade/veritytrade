<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\HomepageHero;

class HomeController extends Controller
{
    public function index()
    {
        $categories = collect();
        $deals = collect();
        $hero = null;

        if (class_exists(\App\Models\Category::class)) {
            try {
                $categories = \App\Models\Category::where('is_active', true)
                    ->orderBy('position')
                    ->get();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $deals = Deal::query()
                ->with('images')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderBy('position', 'desc')
                ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expires_at', 'desc')
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $hero = HomepageHero::get();
        } catch (\Throwable $e) {
            report($e);
        }

        return view('public.home', compact('categories', 'deals', 'hero'));
    }
}
