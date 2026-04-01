<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealImage;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->withCount('images')
            ->latest()
            ->paginate(20);

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product): View
    {
        $product->loadMissing('images');
        $previousProduct = Product::query()
            ->where('id', '>', $product->id)
            ->orderBy('id')
            ->first();
        $nextProduct = Product::query()
            ->where('id', '<', $product->id)
            ->orderByDesc('id')
            ->first();

        $dealBodyPreview = $this->buildDealDescription($product);

        return view('admin.products.show', compact('product', 'previousProduct', 'nextProduct', 'dealBodyPreview'));
    }

    public function edit(Product $product): View
    {
        $product->loadMissing('images');

        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'formatted_listing' => ['nullable', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'price_ngn' => ['nullable', 'integer', 'min:0'],
            'description_en' => ['nullable', 'string'],
            'specs_json_text' => ['nullable', 'string'],
            'condition_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,archived'],
            'stock' => ['required', 'integer', 'min:0'],
            'images.*' => ['nullable', 'file', 'max:4096'],
        ]);

        $parsed = null;
        if (filled($validated['formatted_listing'] ?? null)) {
            $parsed = $this->parseFormattedListing((string) $validated['formatted_listing']);
        }

        $specs = null;
        if ($parsed && $parsed['specs_json'] !== []) {
            $specs = $parsed['specs_json'];
        } elseif (filled($validated['specs_json_text'] ?? null)) {
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

        $title = $parsed['title'] ?? ($validated['title'] ?? $product->title);
        $priceNgn = $parsed['price_ngn'] ?? ($validated['price_ngn'] ?? $product->price_ngn);
        if (blank($title) || ! is_numeric((string) $priceNgn)) {
            return back()
                ->withErrors(['formatted_listing' => 'Provide at least title and price.'])
                ->withInput();
        }

        $product->update([
            'title' => (string) $title,
            'price_ngn' => (int) $priceNgn,
            'description_en' => $parsed['description_en'] ?? ($validated['description_en'] ?? null),
            'specs_json' => $specs,
            'condition_notes' => $parsed['condition_notes'] ?? ($validated['condition_notes'] ?? null),
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
        try {
            $deal = DB::transaction(function () use ($product) {
                $product->update(['status' => 'active']);

                $canLinkSourceProduct = Schema::hasColumn('deals', 'source_product_id');
                $deal = $canLinkSourceProduct
                    ? Deal::query()->firstOrNew(['source_product_id' => $product->id])
                    : new Deal();

                if (! $deal->exists) {
                    $deal->position = ((int) Deal::max('position')) + 1;
                    $deal->created_by = Auth::id();
                }

                $deal->title = $product->title;
                $deal->description = $this->buildDealDescription($product);
                $deal->price_display = '₦' . number_format((int) $product->price_ngn);
                $deal->whatsapp_message = null;
                $deal->expires_at = $deal->expires_at ?? now()->addDays(7);
                $deal->is_active = true;
                if ($canLinkSourceProduct) {
                    $deal->source_product_id = $product->id;
                }
                $deal->save();

                $this->syncDealImagesFromProduct($product, $deal);

                return $deal;
            });

            return redirect()
                ->route('admin.deals.edit', ['deal' => $deal, 'from_product' => $product->id])
                ->with('success', 'Approved. Review and publish in Hot Deals.');
        } catch (\Throwable $e) {
            Log::error('Product approval to hot deal failed', [
                'product_id' => $product->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not approve to hot deal. Please refresh and try again.');
        }
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

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    private function parseFormattedListing(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
        $title = '';
        $descriptionLines = [];
        $specs = [];
        $condition = [];
        $mode = 'desc';
        $price = null;

        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '') {
                continue;
            }
            if ($title === '') {
                $title = preg_replace('/^[•\-\s\p{So}]+/u', '', $line) ?: $line;
                continue;
            }
            if (strtolower($line) === 'specifications:') {
                $mode = 'spec';
                continue;
            }
            if (strtolower($line) === 'condition notes:') {
                $mode = 'cond';
                continue;
            }
            if (preg_match('/^💰\s*Price:\s*₦\s*([\d,]+(?:\.\d+)?)\s*([kKmM])?$/u', $line, $m)) {
                $num = (float) str_replace(',', '', $m[1]);
                $suffix = strtolower($m[2] ?? '');
                if ($suffix === 'm') {
                    $price = (int) round($num * 1000000);
                } elseif ($suffix === 'k') {
                    $price = (int) round($num * 1000);
                } else {
                    $price = (int) round($num);
                }
                continue;
            }

            if ($mode === 'spec' && str_starts_with($line, '•') && str_contains($line, ':')) {
                [$k, $v] = array_map('trim', explode(':', ltrim($line, "• \t"), 2));
                if ($k !== '' && $v !== '') {
                    $specs[$k] = $v;
                }
                continue;
            }
            if ($mode === 'cond' && str_starts_with($line, '•')) {
                $condition[] = trim($line);
                continue;
            }
            $descriptionLines[] = $line;
        }

        return [
            'title' => $title,
            'price_ngn' => $price,
            'description_en' => $descriptionLines ? implode("\n", $descriptionLines) : null,
            'specs_json' => $specs,
            'condition_notes' => $condition ? implode("\n", $condition) : null,
        ];
    }

    private function buildDealDescription(Product $product): string
    {
        $lines = [$product->title, ''];

        if (is_array($product->specs_json) && $product->specs_json !== []) {
            $lines[] = 'Specifications:';
            foreach ($product->specs_json as $key => $value) {
                $lines[] = '• ' . trim((string) $key) . ': ' . trim((string) $value);
            }
            $lines[] = '';
        }

        if (filled($product->condition_notes)) {
            $lines[] = 'Condition Notes:';
            $notes = preg_split('/\r\n|\r|\n/', (string) $product->condition_notes) ?: [];
            foreach ($notes as $note) {
                $note = trim($note);
                if ($note === '') {
                    continue;
                }
                $lines[] = str_starts_with($note, '•') ? $note : '• ' . $note;
            }
            $lines[] = '';
        }

        $extraDesc = $this->extraDescriptionForDeal($product);
        if (filled($extraDesc)) {
            $lines[] = $extraDesc;
            $lines[] = '';
        }

        $lines[] = '💰 Price: ₦' . number_format((int) $product->price_ngn);

        return trim(implode("\n", $lines));
    }

    /**
     * Strip lines from description_en that duplicate the product title (common after ingest / Gemini).
     */
    private function extraDescriptionForDeal(Product $product): ?string
    {
        if (! filled($product->description_en)) {
            return null;
        }

        $title = trim((string) $product->title);
        $rawLines = preg_split('/\r\n|\r|\n/', (string) $product->description_en) ?: [];
        $kept = [];

        foreach ($rawLines as $line) {
            $t = trim($line);
            if ($t === '') {
                continue;
            }
            if (strcasecmp($t, $title) === 0) {
                continue;
            }
            $kept[] = $line;
        }

        if ($kept === []) {
            return null;
        }

        return trim(implode("\n", $kept));
    }

    private function syncDealImagesFromProduct(Product $product, Deal $deal): void
    {
        foreach ($deal->images as $existingImage) {
            if (Storage::disk('public')->exists($existingImage->image_path)) {
                Storage::disk('public')->delete($existingImage->image_path);
            }
            $existingImage->delete();
        }

        $images = $product->images()->orderBy('position')->get();
        foreach ($images as $index => $image) {
            $oldPath = (string) $image->image_path;
            $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
            if ($ext === '') {
                $ext = 'jpg';
            }
            $newPath = 'deals/' . Str::random(40) . '.' . $ext;
            $copied = Storage::disk('public')->copy($oldPath, $newPath);
            if (! $copied) {
                continue;
            }

            DealImage::create([
                'deal_id' => $deal->id,
                'image_path' => $newPath,
                'position' => $index,
            ]);
        }
    }
}
