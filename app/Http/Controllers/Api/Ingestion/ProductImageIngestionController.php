<?php

namespace App\Http\Controllers\Api\Ingestion;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageIngestionController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'max:4096'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $imagePath = $request->file('image')->store('products', 'public');
        $position = $validated['position'] ?? ((int) ($product->images()->max('position') ?? -1) + 1);

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $imagePath,
            'position' => $position,
        ]);

        return response()->json([
            'ok' => true,
            'image' => [
                'id' => $image->id,
                'product_id' => $image->product_id,
                'position' => $image->position,
                'image_url' => storage_asset($image->image_path),
            ],
        ], 201);
    }
}
