<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white sticky top-0 z-10 shadow-sm px-4 py-3 flex items-center gap-3">
            <a href="{{ route('home') }}" class="text-blue-600 font-semibold">Back</a>
            <h1 class="text-lg font-bold text-gray-800">{{ $category->name }} Brands</h1>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-6">
            @if($brands->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
                    <p class="font-semibold text-gray-700">No active brands yet for {{ $category->name }}.</p>
                    <a href="{{ route('public.categories.request.form', ['categorySlug' => \Illuminate\Support\Str::slug($category->name)]) }}"
                       class="mt-4 inline-flex min-h-11 items-center justify-center rounded-lg border border-blue-200 px-4 text-blue-700 font-semibold">
                        Request {{ $category->name }} Item
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($brands as $brand)
                        <a href="{{ route('public.categories.brand', ['categorySlug' => \Illuminate\Support\Str::slug($category->name), 'brandUuid' => $brand->uuid]) }}"
                           class="bg-white rounded-2xl shadow-sm p-4 flex flex-col items-center justify-center min-h-[150px] hover:bg-blue-50 transition-all">
                            @php($brandImage = $brand->representative_image ?: $brand->image_path)
                            @if($brandImage)
                                <img src="{{ asset('storage/' . $brandImage) }}" alt="{{ $brand->name }}" class="h-16 w-16 md:h-20 md:w-20 object-contain mb-2">
                            @else
                                <div class="h-16 w-16 md:h-20 md:w-20 rounded-xl bg-gray-100 mb-2"></div>
                            @endif
                            <div class="text-sm font-semibold text-gray-700 text-center leading-tight line-clamp-2">{{ $brand->name }}</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

