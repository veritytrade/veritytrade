<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white sticky top-0 z-10 shadow-sm">
            <div class="overflow-x-auto scrollbar-hide">
                <div class="flex min-w-max px-2">
                    <a href="{{ route('home') }}" class="block flex-shrink-0 px-4 py-3 text-gray-500 hover:text-gray-700 whitespace-nowrap">
                        Hot Deals
                    </a>

                    @foreach($categories as $category)
                        @php
                            $isPhones = Str::contains(Str::lower((string) $category->name), 'phone');
                        @endphp
                        <a
                            href="{{ $isPhones ? route('public.phones.brands') : route('home') . '#' . Str::slug($category->name) }}"
                            class="block flex-shrink-0 px-4 py-3 whitespace-nowrap {{ $isPhones ? 'font-bold text-blue-600 border-b-[3px] border-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-4">
            <h1 class="text-xl font-bold text-gray-800 mb-4 px-2">Select Phone Brand</h1>

            @if($brands->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500 mx-2">
                    <p>No phone brands available right now. Check back soon.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 px-2">
                    @foreach($brands as $brand)
                        <a href="{{ route('public.phones.brand', ['brandUuid' => $brand->uuid]) }}"
                           class="bg-white rounded-2xl shadow-sm p-4 flex flex-col items-center justify-center min-h-[140px] md:min-h-[170px] hover:bg-blue-50 transition-all">
                            @php($brandImage = $brand->representative_image ?: $brand->image_path)
                            @if($brandImage)
                                <img src="{{ asset('storage/' . $brandImage) }}"
                                     alt="{{ $brand->name }}"
                                     class="h-16 w-16 md:h-20 md:w-20 object-contain mb-2">
                            @else
                                <div class="h-16 w-16 md:h-20 md:w-20 rounded-xl bg-gray-100 mb-2"></div>
                            @endif
                            <div class="text-sm font-semibold text-gray-700 text-center leading-tight line-clamp-2">
                                {{ $brand->name }}
                            </div>
                        </a>
                    @endforeach
                </div>
                @if($brands->isNotEmpty())
                    <div class="px-2 mt-4">
                        <a href="{{ route('public.phones.request.form', ['brandUuid' => $brands->first()->uuid]) }}"
                           class="w-full min-h-12 border border-blue-200 text-blue-700 font-semibold rounded-xl flex items-center justify-center">
                            Request a Phone Model
                        </a>
                    </div>
                @endif
            @endif

            <div class="mt-8 text-center text-xs text-gray-500 px-2 pb-16">
                <p>Tap any brand to see available models and prices</p>
            </div>
        </div>

        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
    </div>
</x-app-layout>
