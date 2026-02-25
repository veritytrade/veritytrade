<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white sticky top-0 z-10 shadow-sm px-4 py-3 flex items-center gap-3">
            <a href="{{ route('public.categories.show', ['categorySlug' => \Illuminate\Support\Str::slug($category->name)]) }}" class="text-blue-600 font-semibold">Back</a>
            <h1 class="text-lg font-bold text-gray-800">{{ $brand->name }}</h1>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-5 space-y-4">
            @if($series->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="text-base font-bold text-blue-700 mb-3">Series</h2>
                    <div class="space-y-3">
                        @foreach($series as $seriesItem)
                            <div class="border border-gray-200 rounded-xl p-3">
                                <div class="flex items-center gap-3">
                                    @php($seriesImage = $seriesItem->representative_image ?: $seriesItem->image_path)
                                    @if($seriesImage)
                                        <img src="{{ asset('storage/' . $seriesImage) }}" alt="{{ $seriesItem->name }}" class="h-14 w-14 rounded-lg object-cover">
                                    @else
                                        <div class="h-14 w-14 rounded-lg bg-gray-100"></div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $seriesItem->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $seriesItem->devices->count() }} model(s)</div>
                                    </div>
                                </div>

                                @if($seriesItem->devices->isNotEmpty())
                                    <div class="mt-3">
                                        <label class="text-xs font-semibold text-gray-600">Available Models</label>
                                        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            <option value="">Select model</option>
                                            @foreach($seriesItem->devices as $model)
                                                <option>{{ $model->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($directModels->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="text-base font-bold text-blue-700 mb-3">Models (No Series)</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($directModels as $model)
                            <div class="border border-gray-200 rounded-xl p-3">
                                <div class="font-semibold text-gray-800">{{ $model->name }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($series->isEmpty() && $directModels->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
                    <p class="font-semibold text-gray-700">No published models yet for {{ $brand->name }}.</p>
                </div>
            @endif

            <a href="{{ route('public.categories.request.form', ['categorySlug' => \Illuminate\Support\Str::slug($category->name)]) }}?brand_id={{ $brand->id }}"
               class="w-full min-h-12 border border-blue-200 text-blue-700 font-semibold rounded-xl flex items-center justify-center bg-white">
                Request {{ $brand->name }} {{ $category->name }} Item
            </a>
        </div>
    </div>
</x-app-layout>

