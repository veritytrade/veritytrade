<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Support\Collection;

class LandingController extends Controller
{
    public function index()
    {
        $deals = collect();
        $categories = new Collection;
        $phoneBrands = new Collection;

        try {
            $deals = Deal::with('images')
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->orderBy('expires_at', 'asc')
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        if (class_exists('App\Models\Category') && class_exists('App\Models\Brand')) {
            try {
                $categories = \App\Models\Category::orderBy('name')->get();
                $phoneCategory = \App\Models\Category::where('name', 'Phones')->first();
                $phoneBrands = $phoneCategory
                    ? \App\Models\Brand::where('category_id', $phoneCategory->id)->orderBy('name')->get()
                    : collect();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('landing.index', compact('deals', 'categories', 'phoneBrands'));
    }

    public function whatsapp(Deal $deal)
    {
        $message = $deal->whatsapp_message ?? 
                   "Hello, I'm interested in this hot deal:\n\n" .
                   "*{$deal->title}*\n" .
                   "Price: {$deal->price_display}\n\n" .
                   "Is it still available?";

        $whatsappNumber = site_setting('whatsapp_number', site_setting('whatsapp_business_number', '2347084117779'));
        
        return redirect("https://wa.me/{$whatsappNumber}?text=" . urlencode($message));
    }

    public function show(Deal $deal)
    {
        $deal->loadMissing('images');

        // Suggestions exclude the current deal.
        $suggestions = collect();
        try {
            $suggestions = Deal::with('images')
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->where('id', '!=', $deal->id)
                ->orderBy('expires_at', 'asc')
                ->take(8)
                ->get();
        } catch (\Throwable $e) {
            report($e);
            $suggestions = collect();
        }

        return view('landing.deal', [
            'deal' => $deal,
            'suggestions' => $suggestions,
        ]);
    }
}
