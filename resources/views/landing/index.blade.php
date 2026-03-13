<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <!-- Tabs Navigation -->
        <div class="bg-white sticky top-0 z-10 shadow-md">
            <div class="overflow-x-auto scrollbar-hide">
                <div class="flex min-w-max px-4">
                    <a href="#hot-deals" 
                       class="block flex-shrink-0 px-5 py-4 text-sm font-bold text-white bg-gradient-to-r from-green-600 to-blue-600 rounded-t-lg whitespace-nowrap shadow-sm">
                        🔥 Hot Deals
                    </a>
                    <a href="#phones" class="block flex-shrink-0 px-5 py-4 text-sm font-medium text-gray-600 hover:text-green-600 whitespace-nowrap transition-colors">
                        Phones
                    </a>
                    <a href="#laptops" class="block flex-shrink-0 px-5 py-4 text-sm font-medium text-gray-600 hover:text-green-600 whitespace-nowrap transition-colors">
                        Laptops
                    </a>
                    <a href="#tablets" class="block flex-shrink-0 px-5 py-4 text-sm font-medium text-gray-600 hover:text-green-600 whitespace-nowrap transition-colors">
                        Tablets
                    </a>
                    <a href="#consoles" class="block flex-shrink-0 px-5 py-4 text-sm font-medium text-gray-600 hover:text-green-600 whitespace-nowrap transition-colors">
                        Consoles
                    </a>
                </div>
            </div>
        </div>

        <!-- Hot Deals Section -->
        <div id="hot-deals" class="max-w-6xl mx-auto px-4 py-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-6 text-center">
                <span class="bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent"> Today's Hot Deals</span>
            </h1>

            @if($deals->isEmpty())
                <div class="bg-white rounded-2xl shadow-lg p-10 text-center">
                    <div class="text-6xl mb-4">⏳</div>
                    <p class="font-bold text-xl text-gray-800">No hot deals available</p>
                    <p class="text-sm mt-2 text-gray-500">Check back soon for exclusive offers!</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($deals as $deal)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1" 
                             x-data="{ ...dealCarousel(@js(($deal->images ?? collect())->pluck('image_path')->map(fn($p) => asset('storage/' . $p))->values()->toArray())), specsOpen: false }"
                             @touchstart.passive="handleTouchStart($event)"
                             @touchend="handleTouchEnd($event)">

                            {{-- Image Carousel --}}
                            <div class="relative aspect-square bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden rounded-t-2xl">
                                <template x-if="images.length === 0">
                                    <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                        <span class="text-gray-400 text-6xl">📱</span>
                                    </div>
                                </template>
                                
                                <template x-if="images.length > 0">
                                    <div class="relative w-full h-full">
                                        <template x-for="(image, index) in images" :key="index">
                                            <div x-show="current === index" 
                                                 class="absolute inset-0 transition-opacity duration-500"
                                                 x-transition:enter="transition-opacity duration-500"
                                                 x-transition:enter-start="opacity-0"
                                                 x-transition:enter-end="opacity-100"
                                                 x-transition:leave="transition-opacity duration-500"
                                                 x-transition:leave-start="opacity-100"
                                                 x-transition:leave-end="opacity-0">
                                                <img :src="image" 
                                                     :alt="'{{ addslashes(str_replace(["\r","\n"], ' ', $deal->title)) }} image ' + (index + 1)"
                                                     class="w-full h-full object-contain object-center p-4 bg-white"
                                                     width="400"
                                                     height="400"
                                                     loading="lazy"
                                                     fetchpriority="{{ $loop->index < 2 ? 'high' : 'auto' }}">
                                            </div>
                                        </template>
                                        
                                        {{-- Navigation Dots --}}
                                        <div x-show="images.length > 1" class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                                            <template x-for="(img, index) in images" :key="index">
                                                <button @click="current = index; resetAutoRotate()" 
                                                        class="w-3 h-3 rounded-full transition-all shadow-sm"
                                                        :class="current === index ? 'bg-gradient-to-r from-green-500 to-green-600 scale-125' : 'bg-white/70 hover:bg-white'">
                                                </button>
                                            </template>
                                        </div>
                                        
                                        {{-- Image Counter --}}
                                        <div x-show="images.length > 1" class="absolute top-3 right-3 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full shadow-lg">
                                            <span x-text="current + 1"></span>/<span x-text="images.length"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Content -->
                            <div class="p-4">
                                {{-- Title --}}
                                <h2 class="text-base sm:text-lg font-bold text-blue-600 mb-0.5 text-center line-clamp-2">
                                    {{ $deal->title }}
                                </h2>

                                {{-- Price --}}
                                @if($deal->price_display)
                                    @php
                                        $p = preg_replace('/\s+/u', '', trim((string) $deal->price_display));
                                        $pVal = preg_replace('/^(?:₦|NGN|N)/u', '', $p);
                                        $pVal = $pVal !== '' ? $pVal : $p;
                                    @endphp
                                    <div class="text-xl sm:text-2xl font-extrabold text-green-600 mb-3 text-center"><span>₦{{ $pVal }}</span></div>
                                @endif

                                {{-- Spec Boxes (collapsible) --}}
                                @php
                                    $lines = array_filter(explode("\n", $deal->description));
                                    $excludeKeys = ['Model', 'Price'];
                                    $specLinesLanding = collect($lines)->map(function ($line) use ($excludeKeys) {
                                        $parts = explode(':', $line, 2);
                                        $key = isset($parts[1]) ? trim($parts[0]) : '';
                                        $value = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
                                        foreach ($excludeKeys as $exclude) {
                                            if (stripos($key, $exclude) !== false) return null;
                                        }
                                        return $value ? ['key' => $key, 'value' => $value] : null;
                                    })->filter()->values();
                                @endphp

                                @if($specLinesLanding->isNotEmpty())
                                    <div class="mb-3">
                                        <button type="button"
                                                @click="specsOpen = !specsOpen"
                                                class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 text-sm font-medium text-blue-600 transition-colors">
                                            <span x-text="specsOpen ? 'Hide details' : 'Show details'"></span>
                                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': specsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="specsOpen"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100"
                                             x-transition:leave-end="opacity-0"
                                             class="space-y-1.5 mt-2">
                                            @foreach($specLinesLanding as $spec)
                                                <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-gray-100 rounded-lg p-2.5 hover:shadow-md transition-all duration-200">
                                                    <div class="text-xs text-blue-600 font-semibold mb-1 uppercase tracking-wide">{{ $spec['key'] }}</div>
                                                    <div class="text-sm text-gray-800 font-bold">{{ $spec['value'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- CTA Button --}}
                                @if(filled($deal->uuid))
                                    <a href="{{ route('deal.whatsapp', ['deal' => $deal->uuid]) }}"
                                       class="w-full min-h-12 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 text-sm sm:text-base rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                        <span>WhatsApp to Buy Now</span>
                                    </a>
                                @else
                                    <button type="button" disabled
                                            class="w-full min-h-12 bg-gray-300 text-gray-600 font-bold py-3 text-sm sm:text-base rounded-xl cursor-not-allowed">
                                        WhatsApp Unavailable
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Footer + Chat Now --}}
            <div class="mt-10 text-center pb-8">
                <p class="text-xs text-gray-500">💬 Secure transactions via WhatsApp only</p>
                <p class="mt-3 text-sm text-gray-600">Laptops, Tablets, Gaming Consoles, or need agency assistance for China imports?</p>
                <a href="https://wa.me/2347084117779?text=Hi%2C%20I%27m%20interested%20in%20laptops%2C%20tablets%2C%20gaming%20consoles%2C%20or%20agency%20assistance%20for%20China%20imports."
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center mt-4 gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
                    Chat Now on WhatsApp
                </a>
            </div>
        </div>

        {{-- Placeholder sections so tab anchors work (content coming soon) --}}
        <div id="phones" class="max-w-6xl mx-auto px-4 py-12 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Phones</h2>
            <p class="text-center text-gray-500">Coming soon.</p>
        </div>
        <div id="laptops" class="max-w-6xl mx-auto px-4 py-12 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Laptops</h2>
            <p class="text-center text-gray-500">Coming soon.</p>
        </div>
        <div id="tablets" class="max-w-6xl mx-auto px-4 py-12 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Tablets</h2>
            <p class="text-center text-gray-500">Coming soon.</p>
        </div>
        <div id="consoles" class="max-w-6xl mx-auto px-4 py-12 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Consoles</h2>
            <p class="text-center text-gray-500">Coming soon.</p>
        </div>

        {{-- Sticky WhatsApp Button --}}
        <div class="fixed bottom-5 right-5 md:hidden z-50">
            <a href="https://wa.me/2347084117779"
               target="_blank"
               class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-2xl transition-transform hover:scale-110">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </a>
        </div>
    </div>

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <script>
    function dealCarousel(images) {
        return {
            images: images,
            current: 0,
            touchStart: 0,
            autoRotateTimer: null,
            init() {
                if (this.images.length > 1) {
                    this.startAutoRotate();
                }
            },
            startAutoRotate() {
                this.autoRotateTimer = setInterval(() => {
                    this.current = (this.current + 1) % this.images.length;
                }, 4000);
            },
            resetAutoRotate() {
                if (this.autoRotateTimer) {
                    clearInterval(this.autoRotateTimer);
                }
                if (this.images.length > 1) {
                    this.startAutoRotate();
                }
            },
            next() {
                this.current = (this.current + 1) % this.images.length;
            },
            prev() {
                this.current = (this.current - 1 + this.images.length) % this.images.length;
            },
            handleTouchStart(event) {
                this.touchStart = event.touches[0].clientX;
            },
            handleTouchEnd(event) {
                const touchEnd = event.changedTouches[0].clientX;
                const diff = this.touchStart - touchEnd;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) { this.next(); } else { this.prev(); }
                    this.resetAutoRotate();
                }
            }
        }
    }
    </script>
</x-app-layout>
