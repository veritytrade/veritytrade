@props(['deals'])

<div id="hot-deals" class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Hot Deals</h1>

    @if($deals->isEmpty())
        <div class="block p-10 bg-white border border-gray-200 rounded-xl shadow-sm text-center text-gray-500">
            <p class="font-semibold text-lg text-gray-800">No hot deals available right now</p>
            <p class="text-sm mt-2">Check back soon for fresh offers.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($deals as $deal)
                @php
                    $imageUrls = $deal->images->pluck('image_path')->map(fn ($path) => asset('storage/' . $path))->values();

                    $specLines = collect(explode("\n", (string) $deal->description))
                        ->map(fn ($line) => trim($line))
                        ->filter()
                        ->map(function ($line) {
                            [$label, $value] = array_pad(explode(':', $line, 2), 2, '');
                            return ['label' => trim($label), 'value' => trim($value)];
                        })
                        ->filter(fn ($spec) => $spec['value'] !== '' && !in_array(strtolower($spec['label']), ['model', 'price'], true))
                        ->values();

                    $hoursLeft = $deal->expires_at ? now()->diffInHours($deal->expires_at, false) : null;
                    $isUrgent = $hoursLeft !== null && $hoursLeft > 0 && $hoursLeft <= 24;

                    $priceText = trim((string) $deal->price_display);
                    $priceValue = preg_replace('/^\s*(?:₦|NGN)\s*/u', '', $priceText);
                    $priceValue = $priceValue !== '' ? $priceValue : $priceText;
                    $dealWhatsappUrl = filled($deal->uuid)
                        ? route('deal.whatsapp', ['deal' => $deal->uuid])
                        : null;
                @endphp

                <article class="block bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-green-200 transition overflow-hidden">
                    <div
                        x-data="{
                            images: @js($imageUrls),
                            current: 0,
                            touchStartX: 0,
                            timer: null,
                            init() {
                                if (this.images.length > 1) {
                                    this.timer = setInterval(() => this.next(), 4000);
                                }
                            },
                            next() {
                                this.current = (this.current + 1) % this.images.length;
                            },
                            prev() {
                                this.current = (this.current - 1 + this.images.length) % this.images.length;
                            },
                            resetTimer() {
                                if (this.timer) {
                                    clearInterval(this.timer);
                                    this.timer = setInterval(() => this.next(), 4000);
                                }
                            },
                            onTouchStart(event) {
                                this.touchStartX = event.touches[0].clientX;
                            },
                            onTouchEnd(event) {
                                const endX = event.changedTouches[0].clientX;
                                const deltaX = this.touchStartX - endX;
                                if (Math.abs(deltaX) > 50 && this.images.length > 1) {
                                    deltaX > 0 ? this.next() : this.prev();
                                    this.resetTimer();
                                }
                            }
                        }"
                        @touchstart.passive="onTouchStart($event)"
                        @touchend="onTouchEnd($event)"
                        class="relative aspect-square bg-white border-b border-gray-200 overflow-hidden"
                    >
                        @if($isUrgent)
                            <div class="absolute top-3 left-3 z-20 bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full animate-pulse">
                                Ends Soon
                            </div>
                        @endif

                        <template x-if="images.length === 0">
                            <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                <span class="text-sm">No image</span>
                            </div>
                        </template>

                        <template x-for="(image, index) in images" :key="index">
                            <div x-show="current === index" class="absolute inset-0" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                <img :src="image" :alt="'{{ $deal->title }} image ' + (index + 1)" class="w-full h-full object-contain p-5 bg-white" loading="lazy">
                            </div>
                        </template>

                        <div x-show="images.length > 1" class="absolute bottom-3 left-0 right-0 z-20 flex justify-center gap-2">
                            <template x-for="(image, index) in images" :key="'dot-' + index">
                                <button type="button" @click="current = index; resetTimer()" class="w-2.5 h-2.5 rounded-full transition-all" :class="current === index ? 'bg-white scale-110' : 'bg-white/60'" aria-label="Go to image"></button>
                            </template>
                        </div>
                    </div>

                    <div class="p-5">
                        <h2 class="text-lg font-bold text-blue-600 text-center line-clamp-2 min-h-[3.5rem]">{{ $deal->title }}</h2>

                        @if($deal->price_display)
                            <div class="text-center text-[28px] leading-tight text-green-600 mt-2 mb-4">
                                <span class="font-medium">₦</span>
                                <span class="font-extrabold">{{ $priceValue }}</span>
                            </div>
                        @endif

                        @if($specLines->isNotEmpty())
                            <div class="space-y-2 mb-5">
                                @foreach($specLines as $spec)
                                    <div class="w-full rounded-lg border border-gray-200 bg-[#f2f8ff] px-3 py-3 hover:bg-[#eaf4ff] hover:shadow-sm transition-all">
                                        <div class="text-xs font-semibold tracking-wide text-blue-600 uppercase">{{ $spec['label'] }}</div>
                                        <div class="text-sm font-bold text-gray-800 mt-0.5 break-words">{{ $spec['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($dealWhatsappUrl)
                            <a href="{{ $dealWhatsappUrl }}" class="w-full min-h-14 bg-green-600 hover:bg-green-700 text-white font-bold text-base rounded-xl flex items-center justify-center gap-2 focus:ring-4 focus:ring-green-300 transition">
                                <span>WhatsApp to Buy Now</span>
                            </a>
                        @else
                            <button type="button" disabled class="w-full min-h-14 bg-gray-300 text-gray-600 font-bold text-base rounded-xl flex items-center justify-center gap-2 cursor-not-allowed">
                                <span>WhatsApp Unavailable</span>
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <div class="mt-8 text-center text-xs text-gray-500 px-2 pb-12">
        <p>Transactions via WhatsApp only.</p>
        <p class="mt-3 text-sm text-gray-600">
            Need something else? Send us a message on WhatsApp with your request.
        </p>
    </div>
</div>
