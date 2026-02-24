<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        @php
            $phoneCategory = $categories->first(fn ($category) => Str::contains(Str::lower((string) $category->name), 'phone'));
            $phoneTabId = $phoneCategory ? Str::slug($phoneCategory->name) : 'phones';
        @endphp

        <div class="bg-white sticky top-0 z-10 shadow-sm">
            <div class="overflow-x-auto scrollbar-hide">
                <div class="flex min-w-max">
                    <a href="#hot-deals" class="block flex-shrink-0 px-4 py-3 font-bold text-green-600 border-b-[3px] border-green-600 whitespace-nowrap">
                        Hot Deals
                    </a>

                    @foreach($categories as $category)
                        <a href="#{{ Str::slug($category->name) }}" class="block flex-shrink-0 px-4 py-3 text-gray-500 hover:text-gray-700 whitespace-nowrap">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <x-hot-deals-section :deals="$deals" />

        <div id="{{ $phoneTabId }}" class="max-w-7xl mx-auto px-4 py-6 hidden">
            <h1 class="text-xl font-bold text-gray-800 mb-4 px-2">Select Phone Brand</h1>

            @if($phoneBrands->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500 mx-2">
                    <p>No phone brands available right now.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 px-2">
                    @foreach($phoneBrands as $brand)
                        <a href="{{ route('public.phones.brand', ['brandUuid' => $brand->uuid]) }}" class="bg-white rounded-2xl shadow-sm p-4 flex flex-col items-center justify-center min-h-[140px] md:min-h-[170px] hover:bg-blue-50 transition-all">
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
                @if($phoneBrands->isNotEmpty())
                    <div class="px-2 mt-4">
                        <a href="{{ route('public.phones.request.form', ['brandUuid' => $phoneBrands->first()->uuid]) }}"
                           class="w-full min-h-12 border border-blue-200 text-blue-700 font-semibold rounded-xl flex items-center justify-center">
                            Request a Phone Model
                        </a>
                    </div>
                @endif
            @endif
        </div>

        @foreach($categories as $category)
            @if(!$phoneCategory || $category->id !== $phoneCategory->id)
                <div id="{{ Str::slug($category->name) }}" class="max-w-7xl mx-auto px-4 py-6 hidden">
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500 mx-2">
                        <h2 class="text-xl font-bold mb-2 text-gray-800">{{ $category->name }}</h2>
                        <p>Products in this category will appear here.</p>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="fixed bottom-4 right-4 md:hidden z-50">
            <a href="https://wa.me/2347084117779" class="bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-105">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/>
                </svg>
            </a>
        </div>

        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('a[href^="#"]');
                const sections = Array.from(tabs)
                    .map((tab) => tab.getAttribute('href').substring(1))
                    .map((id) => document.getElementById(id))
                    .filter(Boolean);

                const activateTab = (targetId) => {
                    sections.forEach((section) => section.classList.add('hidden'));

                    const target = document.getElementById(targetId) || document.getElementById('hot-deals');
                    target.classList.remove('hidden');

                    tabs.forEach((tab) => {
                        const isActive = tab.getAttribute('href') === `#${target.id}`;
                        tab.classList.toggle('text-green-600', isActive);
                        tab.classList.toggle('border-b-[3px]', isActive);
                        tab.classList.toggle('border-green-600', isActive);
                        tab.classList.toggle('font-bold', isActive);
                        tab.classList.toggle('text-gray-500', !isActive);
                    });
                };

                tabs.forEach((tab) => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href').substring(1);
                        history.replaceState(null, '', `#${targetId}`);
                        activateTab(targetId);
                    });
                });

                const initialTarget = window.location.hash ? window.location.hash.substring(1) : 'hot-deals';
                activateTab(initialTarget);
            });
        </script>
    </div>
</x-app-layout>
