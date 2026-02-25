<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\View\View;

class RequestHubController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('public.requests.index', compact('categories'));
    }
}
