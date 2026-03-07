<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageHero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomepageHeroController extends Controller
{
    public function edit(): View
    {
        $hero = HomepageHero::get() ?? new HomepageHero;
        return view('admin.homepage-hero.edit', compact('hero'));
    }

    public function update(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'hero_headline' => 'nullable|string|max:255',
            'hero_subheadline' => 'nullable|string|max:255',
            'hero_cta_text' => 'nullable|string|max:80',
            'hero_cta_url' => 'nullable|string|max:500',
            'hero_visible' => 'boolean',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $hero = HomepageHero::get();
        if (!$hero) {
            $hero = HomepageHero::create([
                'hero_headline' => $valid['hero_headline'] ?? null,
                'hero_subheadline' => $valid['hero_subheadline'] ?? null,
                'hero_cta_text' => $valid['hero_cta_text'] ?? null,
                'hero_cta_url' => $valid['hero_cta_url'] ?? '#hot-deals',
                'hero_visible' => $valid['hero_visible'] ?? true,
            ]);
        }

        if ($request->hasFile('hero_image')) {
            if ($hero->hero_image_path) {
                Storage::disk('public')->delete($hero->hero_image_path);
            }
            $path = $request->file('hero_image')->store('homepage', 'public');
            $hero->hero_image_path = $path;
        }

        $hero->hero_headline = $valid['hero_headline'] ?? $hero->hero_headline;
        $hero->hero_subheadline = $valid['hero_subheadline'] ?? $hero->hero_subheadline;
        $hero->hero_cta_text = $valid['hero_cta_text'] ?? $hero->hero_cta_text;
        $hero->hero_cta_url = $valid['hero_cta_url'] ?? $hero->hero_cta_url;
        $hero->hero_visible = $request->boolean('hero_visible');
        $hero->save();

        return back()->with('success', 'Homepage hero updated.');
    }
}
