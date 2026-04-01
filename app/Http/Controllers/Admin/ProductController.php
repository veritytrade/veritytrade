<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        $products = Product::query()
            ->withCount('images')
            ->when(in_array($status, ['draft', 'active', 'archived'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'statusFilter' => $status,
        ]);
    }

    public function show(Product $product): View
    {
        $product->loadMissing('images');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->loadMissing('images');

        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price_ngn' => ['required', 'integer', 'min:0'],
            'description_en' => ['nullable', 'string'],
            'specs_json_text' => ['nullable', 'string'],
            'condition_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,archived'],
            'stock' => ['required', 'integer', 'min:0'],
            'images.*' => ['nullable', 'file', 'max:4096'],
        ]);

        $specs = null;
        if (filled($validated['specs_json_text'] ?? null)) {
            $rawSpecs = trim((string) $validated['specs_json_text']);
            $decoded = json_decode($rawSpecs, true);

            if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_null($decoded))) {
                $specs = $decoded;
            } else {
                // Mobile-friendly fallback: allow "Key: Value" lines and convert to JSON map.
                $lines = preg_split('/\r\n|\r|\n/', $rawSpecs) ?: [];
                $pairs = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || ! str_contains($line, ':')) {
                        continue;
                    }
                    [$k, $v] = array_map('trim', explode(':', $line, 2));
                    if ($k !== '' && $v !== '') {
                        $pairs[$k] = $v;
                    }
                }

                if ($pairs === []) {
                    return back()
                        ->withErrors(['specs_json_text' => 'Specs must be valid JSON or Key: Value lines.'])
                        ->withInput();
                }
                $specs = $pairs;
            }
        }

        $product->update([
            'title' => $validated['title'],
            'price_ngn' => $validated['price_ngn'],
            'description_en' => $validated['description_en'] ?? null,
            'specs_json' => $specs,
            'condition_notes' => $validated['condition_notes'] ?? null,
            'status' => $validated['status'],
            'stock' => $validated['stock'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                if (! $image) {
                    continue;
                }

                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'position' => ((int) ($product->images()->max('position') ?? -1)) + 1 + $index,
                ]);
            }
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function approve(Product $product): RedirectResponse
    {
        $product->update(['status' => 'active']);

        return back()->with('success', 'Product approved and now visible publicly.');
    }

    public function archive(Product $product): RedirectResponse
    {
        $product->update(['status' => 'archived']);

        return back()->with('success', 'Product archived.');
    }

    public function deleteImage(Product $product, ProductImage $image): RedirectResponse
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return back()->with('success', 'Image removed.');
    }
}
