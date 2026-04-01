<?php

namespace App\Http\Controllers\Api\Ingestion;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductIngestionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price_ngn' => ['required', 'integer', 'min:0'],
            'description_en' => ['nullable', 'string'],
            'specs_json' => ['nullable', 'array'],
            'condition_notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'active', 'archived'])],
            'stock' => ['required', 'integer', 'min:0'],
            'source_site' => ['required', 'string', 'max:100'],
            'source_item_id' => ['required', 'string', 'max:191'],
            'source_url_private' => ['nullable', 'string', 'max:2048'],
        ]);

        // Safety-first publishing: ingestion always lands as draft for admin review.
        $validated['status'] = 'draft';

        $product = Product::updateOrCreate(
            [
                'source_site' => $validated['source_site'],
                'source_item_id' => $validated['source_item_id'],
            ],
            $validated
        )->load('images');

        return response()->json([
            'ok' => true,
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'status' => $product->status,
                'stock' => $product->stock,
                'source_site' => $product->source_site,
                'source_item_id' => $product->source_item_id,
                'images_count' => $product->images->count(),
            ],
        ], 201);
    }
}
