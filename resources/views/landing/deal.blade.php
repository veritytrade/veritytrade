<x-app-layout>
    @php
        $imageUrls = ($deal->images ?? collect())
            ->pluck('image_path')
            ->map(fn ($p) => storage_asset($p))
            ->values();

        $priceText = preg_replace('/\s+/u', '', trim((string) ($deal->price_display ?? '')));
        $priceValue = preg_replace('/^(?:₦|NGN|N)/u', '', $priceText);
        $priceValue = $priceValue !== '' ? $priceValue : $priceText;

        $specLines = collect(explode("\n", (string) $deal->description))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->map(function ($line) {
                [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
                $key = trim($key);
                $value = trim($value);
                return ['key' => $key, 'value' => $value];
            })
            ->filter(function ($spec) {
                $key = strtolower((string) $spec['key']);
                $blocked = ['model', 'price', 'cost', 'amount'];
                return $spec['key'] !== '' && $spec['value'] !== '' && !in_array($key, $blocked, true);
            })
            ->values();

        $buyUrl = filled($deal->uuid) ? route('deal.whatsapp', ['deal' => $deal->uuid]) : null;
    @endphp

    <div class="premium-shell">
        <div class="max-w-4xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
            <div class="mb-4">
                <a href="{{ route('home') }}" class="premium-link text-sm font-medium">&larr; Back</a>
            </div>

            <div class="premium-card p-3 sm:p-5">
                <div class="flex flex-col gap-4">
                    {{-- Image / Carousel --}}
                    <div class="w-full">
                        <div class="relative aspect-square bg-white border border-gray-200 rounded-xl overflow-hidden">
                            @if($imageUrls->isNotEmpty())
                                {{-- Static server-rendered image to ensure it always shows (no JS dependency). --}}
                                <img
                                    src="{{ $imageUrls->first() }}"
                                    alt="{{ $deal->title }} image"
                                    class="w-full h-full object-contain p-4 bg-white"
                                />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    No image
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="w-full flex flex-col">
                        <h1 class="premium-title text-xl sm:text-2xl leading-tight">
                            {{ $deal->title }}
                        </h1>

                        @if(!empty($deal->price_display))
                            <div class="text-xl sm:text-2xl font-extrabold text-emerald-700 mt-1">
                                ₦{{ $priceValue !== '' ? $priceValue : '—' }}
                            </div>
                        @endif

                        @if($specLines->isNotEmpty())
                            <div class="mt-3">
                                <div class="border border-gray-200 rounded-xl p-3 bg-slate-50/70"
                                     x-data="{ specsOpen: false }">
                                    <button type="button"
                                            @click="specsOpen = !specsOpen"
                                            class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-100 text-sm font-medium text-emerald-700 transition-colors">
                                        <span x-text="specsOpen ? 'Hide full specs' : 'Show full specs'"></span>
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
                                         class="space-y-2.5 mt-3">
                                        @foreach($specLines as $spec)
                                            <div class="rounded-lg border border-gray-200 bg-gradient-to-r from-sky-50 to-emerald-50 px-3 py-2">
                                                <div class="text-xs font-semibold tracking-wide text-sky-700 uppercase">
                                                    {{ $spec['key'] }}
                                                </div>
                                                <div class="text-sm font-bold text-gray-900 mt-1 break-words">
                                                    {{ $spec['value'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-auto pt-4">
                            @if($buyUrl)
                                <a href="{{ $buyUrl }}"
                                   class="premium-btn-primary w-full min-h-12 text-sm sm:text-base rounded-xl flex gap-2">
                                    <span>WhatsApp to Buy Now</span>
                                </a>
                            @else
                                <button type="button" disabled
                                        class="w-full min-h-12 bg-gray-300 text-gray-600 font-bold text-sm sm:text-base rounded-xl flex items-center justify-center cursor-not-allowed">
                                    WhatsApp Unavailable
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Suggestions --}}
            <div class="mt-6">
                <h2 class="premium-title text-lg sm:text-xl mb-3">More deals you might like</h2>
                @if($suggestions->isEmpty())
                    <div class="premium-card p-4 text-sm text-gray-500">
                        No suggestions right now.
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($suggestions as $s)
                            @php
                                $sImage = ($s->images ?? collect())->first();
                                $sImageUrl = $sImage ? storage_asset($sImage->image_path) : null;
                                $sPriceText = preg_replace('/\s+/u', '', trim((string) ($s->price_display ?? '')));
                                $sPriceValue = preg_replace('/^(?:₦|NGN|N)/u', '', $sPriceText);
                                $sPriceValue = $sPriceValue !== '' ? $sPriceValue : $sPriceText;
                            @endphp
                            <a href="{{ route('deal.show', ['deal' => $s->uuid]) }}"
                               class="premium-card-soft p-2.5 hover:border-emerald-200 hover:shadow-sm transition block">
                                <div class="aspect-square rounded-lg bg-gray-50 overflow-hidden border border-gray-100">
                                    @if($sImageUrl)
                                        <img src="{{ $sImageUrl }}" alt="{{ $s->title }}" class="w-full h-full object-contain p-2"/>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">No image</div>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    <div class="text-xs font-bold text-gray-900 line-clamp-2">
                                        {{ $s->title }}
                                    </div>
                                    @if(!empty($s->price_display))
                                        <div class="text-sm font-extrabold text-emerald-700 mt-1">
                                            ₦{{ $sPriceValue !== '' ? $sPriceValue : '—' }}
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <script>
        function dealCarousel(images) {
            return {
                images: images || [],
                current: 0,
                timer: null,
                touchStart: 0,
                init() {
                    if (this.images.length > 1) {
                        this.startAutoRotate();
                    }
                },
                startAutoRotate() {
                    this.timer = setInterval(() => {
                        this.current = (this.current + 1) % this.images.length;
                    }, 4000);
                },
                resetAutoRotate() {
                    if (this.timer) clearInterval(this.timer);
                    if (this.images.length > 1) this.startAutoRotate();
                },
                handleTouchStart(event) {
                    this.touchStart = event.touches[0].clientX;
                },
                handleTouchEnd(event) {
                    const touchEnd = event.changedTouches[0].clientX;
                    const diff = this.touchStart - touchEnd;
                    if (Math.abs(diff) > 50) {
                        if (diff > 0) {
                            this.current = (this.current + 1) % this.images.length;
                        } else {
                            this.current = (this.current - 1 + this.images.length) % this.images.length;
                        }
                        this.resetAutoRotate();
                    }
                }
            }
        }
    </script>
</x-app-layout>

