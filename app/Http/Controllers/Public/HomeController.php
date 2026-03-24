<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\HomepageHero;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categories = collect();
        $deals = collect();
        $hero = null;
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'min_price' => is_numeric($request->query('min_price')) ? (float) $request->query('min_price') : null,
            'max_price' => is_numeric($request->query('max_price')) ? (float) $request->query('max_price') : null,
            'sort' => (string) $request->query('sort', 'latest'),
        ];

        if (! in_array($filters['sort'], ['latest', 'price_asc', 'price_desc'], true)) {
            $filters['sort'] = 'latest';
        }

        if ($filters['min_price'] !== null && $filters['min_price'] < 0) {
            $filters['min_price'] = 0.0;
        }
        if ($filters['max_price'] !== null && $filters['max_price'] < 0) {
            $filters['max_price'] = null;
        }
        if ($filters['min_price'] !== null && $filters['max_price'] !== null && $filters['max_price'] < $filters['min_price']) {
            [$filters['min_price'], $filters['max_price']] = [$filters['max_price'], $filters['min_price']];
        }

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
            $baseQuery = Deal::query()
                ->with('images')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderBy('position', 'desc')
                ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expires_at', 'desc');

            if ($filters['q'] !== '') {
                $q = $filters['q'];
                $baseQuery->where(function ($query) use ($q) {
                    $query->where('title', 'like', '%' . $q . '%')
                        ->orWhere('description', 'like', '%' . $q . '%')
                        ->orWhere('price_display', 'like', '%' . $q . '%');
                });
            }

            $deals = $baseQuery->get();

            // Numeric price filtering/sorting happens in-memory due mixed price_display formats.
            if ($filters['min_price'] !== null || $filters['max_price'] !== null) {
                $deals = $deals->filter(function ($deal) use ($filters) {
                    $price = $this->parsePriceValue((string) ($deal->price_display ?? ''));
                    if ($price === null) {
                        return false;
                    }
                    if ($filters['min_price'] !== null && $price < $filters['min_price']) {
                        return false;
                    }
                    if ($filters['max_price'] !== null && $price > $filters['max_price']) {
                        return false;
                    }

                    return true;
                })->values();
            }

            if ($filters['sort'] === 'price_asc') {
                $deals = $deals->sortBy(function ($deal) {
                    return $this->parsePriceValue((string) ($deal->price_display ?? '')) ?? PHP_FLOAT_MAX;
                })->values();
            } elseif ($filters['sort'] === 'price_desc') {
                $deals = $deals->sortByDesc(function ($deal) {
                    return $this->parsePriceValue((string) ($deal->price_display ?? '')) ?? -1;
                })->values();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $hero = HomepageHero::get();
        } catch (\Throwable $e) {
            report($e);
        }

        return view('public.home', compact('categories', 'deals', 'hero', 'filters'));
    }

    private function parsePriceValue(string $raw): ?float
    {
        $clean = Str::of($raw)
            ->replaceMatches('/\s+/', '')
            ->replaceMatches('/^(?:₦|NGN|N)/u', '')
            ->replaceMatches('/[^0-9.]/', '')
            ->value();

        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }
}
