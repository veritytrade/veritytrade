<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // Hot Deals
        $deals = Deal::with('images')
                    ->where('is_active', true)
                    ->where('expires_at', '>', now())
                    ->orderBy('expires_at', 'asc')
                    ->get();

        // Categories (for tabs)
        $categories = Category::orderBy('name')->get();

        // Phone Brands (for Phones tab)
        $phoneCategory = Category::where('name', 'Phones')->first();
        $phoneBrands = $phoneCategory ? Brand::where('category_id', $phoneCategory->id)->orderBy('name')->get() : collect();

        return view('landing.index', compact('deals', 'categories', 'phoneBrands'));
    }

    public function whatsapp(Deal $deal)
    {
        $message = $deal->whatsapp_message ?? 
                   "Hello, I'm interested in this hot deal:\n\n" .
                   "*{$deal->title}*\n" .
                   "Price: {$deal->price_display}\n\n" .
                   "Is it still available?";

        $whatsappNumber = site_setting('whatsapp_business_number', '2347084117779');
        
        return redirect("https://wa.me/{$whatsappNumber}?text=" . urlencode($message));
    }
}
