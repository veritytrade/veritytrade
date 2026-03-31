<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->with('images')
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->latest()
            ->get();

        return response()->json([
            'data' => $products->map(fn (Product $product) => $this->publicPayload($product)),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->loadMissing('images');

        if ($product->status !== 'active' || $product->stock < 1) {
            abort(404);
        }

        return response()->json([
            'data' => $this->publicPayload($product),
        ]);
    }

    private function publicPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'title' => $product->title,
            'price_ngn' => $product->price_ngn,
            'description_en' => $product->description_en,
            'specs_json' => $product->specs_json,
            'condition_notes' => $product->condition_notes,
            'status' => $product->status,
            'stock' => $product->stock,
            'images' => $product->images->map(fn ($image) => [
                'id' => $image->id,
                'position' => $image->position,
                'url' => storage_asset($image->image_path),
            ])->values(),
        ];
    }
}
