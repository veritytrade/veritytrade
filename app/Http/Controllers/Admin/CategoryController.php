<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('position')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->whereNull('deleted_at'),
            ],
        ]);

        Category::create([
            'name' => $request->name,
            'is_active' => true,
            'position' => 0,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->whereNull('deleted_at')
                    ->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }

    public function toggle(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Status updated.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }
}
