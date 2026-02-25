<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerRequest;
use App\Models\SpecGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryRequestController extends Controller
{
    public function form(string $categorySlug): View
    {
        $category = Category::where('is_active', true)
            ->get()
            ->first(fn ($item) => Str::slug($item->name) === $categorySlug);

        if (!$category) {
            throw new NotFoundHttpException();
        }

        if (Str::contains(Str::lower($category->name), 'phone')) {
            return redirect()->route('public.phones.brands');
        }

        $brands = Brand::where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $specGroups = SpecGroup::with(['specs.values'])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('public.categories.request', compact('category', 'brands', 'specGroups'));
    }

    public function submit(Request $request, string $categorySlug): RedirectResponse
    {
        $category = Category::where('is_active', true)
            ->get()
            ->first(fn ($item) => Str::slug($item->name) === $categorySlug);

        if (!$category) {
            throw new NotFoundHttpException();
        }

        $validated = $request->validate([
            'brand_id' => 'nullable|exists:brands,id',
            'manual_model_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'request_specs' => 'nullable|array',
        ]);

        $brand = null;
        if (!empty($validated['brand_id'])) {
            $brand = Brand::where('id', $validated['brand_id'])
                ->where('category_id', $category->id)
                ->where('is_active', true)
                ->first();
        }

        $resolvedSpecs = $this->extractRequestSpecs($request, $category->id);

        CustomerRequest::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'category_id' => $category->id,
            'brand_id' => $brand?->id,
            'manual_model_name' => $validated['manual_model_name'],
            'request_spec_json' => $resolvedSpecs,
            'phone_number' => $validated['phone_number'],
            'status' => 'pending',
        ]);

        $message = "Hello, I want to request a product:\n\n";
        $message .= "Category: {$category->name}\n";
        if ($brand) {
            $message .= "Brand: {$brand->name}\n";
        }
        $message .= "Model: {$validated['manual_model_name']}\n";
        foreach ($resolvedSpecs as $label => $value) {
            $message .= "{$label}: {$value}\n";
        }
        $message .= "Contact: {$validated['phone_number']}\n";

        $whatsappNumber = site_setting('whatsapp_number', site_setting('whatsapp_business_number', '2347084117779'));

        return redirect()->away("https://wa.me/{$whatsappNumber}?text=" . urlencode($message));
    }

    private function extractRequestSpecs(Request $request, int $categoryId): array
    {
        $payload = $request->input('request_specs', []);
        if (!is_array($payload)) {
            return [];
        }

        $groups = SpecGroup::with(['specs.values'])
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();

        $resolved = [];

        foreach ($groups as $group) {
            foreach ($group->specs as $spec) {
                $raw = $payload[$spec->id] ?? null;
                if ($raw === null || $raw === '') {
                    continue;
                }

                if ($spec->input_type === 'dropdown') {
                    $value = $spec->values->firstWhere('id', (int) $raw);
                    if ($value) {
                        $resolved[$spec->name] = $value->value;
                    }
                } else {
                    $resolved[$spec->name] = (string) $raw;
                }
            }
        }

        return $resolved;
    }
}
