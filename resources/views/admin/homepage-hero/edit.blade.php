<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6">
        <div class="mb-6">
            <a href="{{ route('admin.dashboard') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">&larr; Dashboard</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Homepage Hero</h2>
            <p class="text-sm text-gray-500 mt-1">Manage the hero section image and text shown on your homepage. Upload an image to display phones or promotional content.</p>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.homepage-hero.update') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                @if($hero->hero_image_path)
                    <div class="mb-3 rounded-lg overflow-hidden border border-gray-200 max-w-sm">
                        <img src="{{ $hero->hero_image_url }}" alt="Current hero" class="w-full h-auto object-contain bg-gray-50">
                    </div>
                    <p class="text-xs text-gray-500 mb-2">Upload a new image to replace. Recommended: 1200×600px or similar landscape. JPEG, PNG, WebP, max 10MB.</p>
                @else
                    <p class="text-sm text-gray-500 mb-2">No image uploaded. Add one for a more impactful hero section. Recommended: 1200×600px or similar landscape.</p>
                @endif
                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/jpg,image/webp"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100 file:font-medium">
                @error('hero_image')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Headline</label>
                <input type="text" name="hero_headline" value="{{ old('hero_headline', $hero->hero_headline) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="e.g. Source Quality Phones Direct From China">
                @error('hero_headline')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subheadline (optional)</label>
                <input type="text" name="hero_subheadline" value="{{ old('hero_subheadline', $hero->hero_subheadline) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="e.g. Premium devices at competitive prices">
                @error('hero_subheadline')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                    <input type="text" name="hero_cta_text" value="{{ old('hero_cta_text', $hero->hero_cta_text) }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="e.g. Browse Deals">
                    @error('hero_cta_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                    <input type="text" name="hero_cta_url" value="{{ old('hero_cta_url', $hero->hero_cta_url) }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="e.g. #hot-deals">
                    <p class="text-xs text-gray-500 mt-1">Use #hot-deals or #phones to scroll to a section on the homepage.</p>
                    @error('hero_cta_url')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="hero_visible" id="hero_visible" value="1"
                       {{ old('hero_visible', $hero->hero_visible) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                <label for="hero_visible" class="text-sm font-medium text-gray-700">Show hero section on homepage</label>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
