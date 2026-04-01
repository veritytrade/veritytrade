<?php

namespace App\Http\Controllers\Api\Ingestion;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductImageIngestionController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'max:4096'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $file = $request->file('image');
        $tmpPath = (string) $file->getRealPath();
        $ext = $this->detectExtension($tmpPath);
        if ($ext === null) {
            abort(422, 'Unsupported image format.');
        }

        $filename = Str::random(40) . '.' . $ext;
        $imagePath = $file->storeAs('products', $filename, 'public');
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

    private function detectExtension(string $path): ?string
    {
        $bytes = @file_get_contents($path, false, null, 0, 16);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return 'jpg';
        }
        if (str_starts_with($bytes, "\x89PNG\x0D\x0A\x1A\x0A")) {
            return 'png';
        }
        if (substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') {
            return 'webp';
        }
        if (strlen($bytes) >= 12 && substr($bytes, 4, 8) === 'ftypavif') {
            return 'avif';
        }

        return null;
    }
}
