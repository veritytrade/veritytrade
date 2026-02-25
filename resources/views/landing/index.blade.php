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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($deals as $deal)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1" 
                             x-data="dealCarousel(@js($deal->images->pluck('image_path')->map(fn($p) => asset('storage/' . $p))->toArray()))"
                             @touchstart.passive="handleTouchStart($event)"
                             @touchend="handleTouchEnd($event)">

                            {{-- Image Carousel --}}
                            <div class="relative aspect-square bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
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
                                                     :alt="'{{ $deal->title }} image ' + (index + 1)"
                                                     class="w-full h-full object-contain p-6 bg-white"
                                                     loading="lazy">
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
                            <div class="p-5">
                                {{-- Title --}}
                                <h2 class="text-lg font-bold text-blue-600 mb-2 text-center line-clamp-2">
                                    {{ $deal->title }}
                                </h2>

                                {{-- Price --}}
                                @if($deal->price_display)
                                    <div class="text-3xl font-extrabold text-green-600 mb-4 text-center">
                                        @if(!str_starts_with(trim($deal->price_display), '₦'))
                                            ₦{{ $deal->price_display }}
                                        @else
                                            {{ $deal->price_display }}
                                        @endif
                                    </div>
                                @endif

                                {{-- Spec Boxes --}}
                                @php
                                    $lines = array_filter(explode("\n", $deal->description));
                                    $excludeKeys = ['Model', 'Price'];
                                @endphp

                                @if(count($lines) > 0)
                                    <div class="space-y-2 mb-5">
                                        @foreach($lines as $line)
                                            @php
                                                $parts = explode(':', $line, 2);
                                                $key = isset($parts[1]) ? trim($parts[0]) : '';
                                                $value = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
                                                $shouldSkip = false;
                                                foreach($excludeKeys as $exclude) {
                                                    if (stripos($key, $exclude) !== false) {
                                                        $shouldSkip = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            
                                            @if($value && !$shouldSkip)
                                                <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-gray-100 rounded-lg p-3 hover:shadow-md transition-all duration-200">
                                                    <div class="text-xs text-blue-600 font-semibold mb-1 uppercase tracking-wide">{{ $key }}</div>
                                                    <div class="text-sm text-gray-800 font-bold">{{ $value }}</div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                {{-- CTA Button --}}
                                @if(filled($deal->uuid))
                                    <a href="{{ route('deal.whatsapp', ['deal' => $deal->uuid]) }}"
                                       class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-4 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                        <span>WhatsApp to Buy Now</span>
                                    </a>
                                @else
                                    <button type="button" disabled
                                            class="w-full bg-gray-300 text-gray-600 font-bold py-4 rounded-xl cursor-not-allowed">
                                        WhatsApp Unavailable
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Footer --}}
            <div class="mt-10 text-center text-xs text-gray-500 pb-8">
                <p class="font-medium">💬 Secure transactions via WhatsApp only</p>
            </div>
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
