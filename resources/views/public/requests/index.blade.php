<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-4">
                <h1 class="text-xl font-bold text-blue-700">Request a Product</h1>
                <p class="text-sm text-gray-600 mt-1">Choose a category to continue your request.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($categories as $category)
                    @php($slug = \Illuminate\Support\Str::slug($category->name))
                    @php($isPhone = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($category->name), 'phone'))
                    <a href="{{ $isPhone ? route('public.phones.brands') : route('public.categories.request.form', ['categorySlug' => $slug]) }}"
                       class="bg-white rounded-xl border border-gray-200 p-4 text-center hover:border-blue-300 hover:bg-blue-50 transition">
                        <div class="font-semibold text-gray-800">{{ $category->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">Make Request</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
