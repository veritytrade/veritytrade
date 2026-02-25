<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Device;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryBrowseController extends Controller
{
    public function show(string $categorySlug): View|RedirectResponse
    {
        $category = $this->resolveCategoryBySlug($categorySlug);

        if ($this->isPhoneCategory($category)) {
            return redirect()->route('public.phones.brands');
        }

        $brands = Brand::where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('public.categories.show', compact('category', 'brands'));
    }

    public function brand(string $categorySlug, string $brandUuid): View|RedirectResponse
    {
        $category = $this->resolveCategoryBySlug($categorySlug);
        $brand = Brand::where('uuid', $brandUuid)->firstOrFail();

        if (!$brand->is_active || (int) $brand->category_id !== (int) $category->id) {
            throw new NotFoundHttpException();
        }

        if ($this->isPhoneCategory($category)) {
            return redirect()->route('public.phones.brand', ['brandUuid' => $brand->uuid]);
        }

        $series = Series::with(['devices' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('position')
                    ->orderBy('name');
            }])
            ->where('brand_id', $brand->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $directModels = Device::where('brand_id', $brand->id)
            ->whereNull('series_id')
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('public.categories.brand', compact('category', 'brand', 'series', 'directModels'));
    }

    private function resolveCategoryBySlug(string $categorySlug): Category
    {
        $category = Category::where('is_active', true)
            ->get()
            ->first(fn ($item) => Str::slug($item->name) === $categorySlug);

        if (!$category) {
            throw new NotFoundHttpException();
        }

        return $category;
    }

    private function isPhoneCategory(Category $category): bool
    {
        return Str::contains(Str::lower((string) $category->name), 'phone');
    }
}

