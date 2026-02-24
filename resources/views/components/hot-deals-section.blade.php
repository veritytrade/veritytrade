@props(['deals'])

<div id="hot-deals" class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-6 px-2">Hot Deals</h1>

    @if($deals->isEmpty())
        <div class="bg-white rounded-2xl shadow-md p-10 text-center text-gray-500 mx-2">
            <p class="font-semibold text-lg text-gray-800">No hot deals available right now</p>
            <p class="text-sm mt-2">Check back soon for fresh offers.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($deals as $deal)
                @php
                    $imageUrls = $deal->images->pluck('image_path')
                        ->map(fn ($path) => asset('storage/' . $path))
                        ->values();

                    $specLines = collect(explode("\n", (string) $deal->description))
                        ->map(fn ($line) => trim($line))
                        ->filter()
                        ->map(function ($line) {
                            [$label, $value] = array_pad(explode(':', $line, 2), 2, '');
                            return [
                                'label' => trim($label),
                                'value' => trim($value),
                            ];
                        })
                        ->filter(function ($spec) {
                            if ($spec['value'] === '') {
                                return false;
                            }

                            return !in_array(strtolower($spec['label']), ['model', 'price'], true);
                        })
                        ->values();

                    $hoursLeft = now()->diffInHours($deal->expires_at, false);
                    $isUrgent = $hoursLeft > 0 && $hoursLeft <= 24;
                @endphp

                <article class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 mx-2 overflow-hidden">
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
                            <div
                                x-show="current === index"
                                x-transition:enter="transition-opacity duration-300"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition-opacity duration-300"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute inset-0"
                            >
                                <img
                                    :src="image"
                                    :alt="'{{ $deal->title }} image ' + (index + 1)"
                                    class="w-full h-full object-contain p-5 bg-white"
                                    loading="lazy"
                                >
                            </div>
                        </template>

                        <div
                            x-show="images.length > 1"
                            class="absolute bottom-3 left-0 right-0 z-20 flex justify-center gap-2"
                        >
                            <template x-for="(image, index) in images" :key="'dot-' + index">
                                <button
                                    type="button"
                                    @click="current = index; resetTimer()"
                                    class="w-2.5 h-2.5 rounded-full transition-all"
                                    :class="current === index ? 'bg-white scale-110' : 'bg-white/60'"
                                    aria-label="Go to image"
                                ></button>
                            </template>
                        </div>

                        <div
                            x-show="images.length > 1"
                            class="absolute top-3 right-3 z-20 bg-black/65 text-white text-xs font-medium px-2.5 py-1 rounded-full"
                        >
                            <span x-text="current + 1"></span>/<span x-text="images.length"></span>
                        </div>
                    </div>

                    <div class="p-5">
                        <h2 class="text-lg font-bold text-blue-600 text-center line-clamp-2 min-h-[3.5rem]">
                            {{ $deal->title }}
                        </h2>

                        @if($deal->price_display)
                            @php
                                $priceText = trim((string) $deal->price_display);
                                $priceValue = preg_replace('/^\s*(₦|N)\s*/u', '', $priceText);
                                $priceValue = $priceValue !== '' ? $priceValue : $priceText;
                            @endphp
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

                        <a
                            href="{{ route('deal.whatsapp', $deal) }}"
                            class="w-full min-h-14 bg-green-600 hover:bg-green-700 text-white font-bold text-base rounded-xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884" />
                            </svg>
                            <span>WhatsApp to Buy Now</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <div class="mt-8 text-center text-xs text-gray-500 px-2 pb-12">
        <p>Transactions via WhatsApp only</p>
    </div>
</div>
