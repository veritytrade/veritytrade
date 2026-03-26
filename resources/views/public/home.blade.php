<x-app-layout>
        <div class="premium-shell">
        @php
            $sections = collect([
                ['id' => 'hot', 'title' => 'Hot Deals', 'deals' => $deals->take(10)],
                ['id' => 'recommend', 'title' => 'Recommended', 'deals' => $deals->slice(0, 8)],
                ['id' => 'new-arrival', 'title' => 'New Arrival', 'deals' => $deals->slice(2, 8)],
                ['id' => 'budget', 'title' => 'Budget Picks', 'deals' => $deals->slice(4, 8)],
                ['id' => 'premium', 'title' => 'Premium Picks', 'deals' => $deals->slice(6, 8)],
            ])->filter(fn ($section) => $section['deals']->isNotEmpty())->values();
        @endphp

        <div class="premium-container py-4 sm:py-6">
            <section class="sticky top-16 z-20 bg-slate-50/95 backdrop-blur pb-2 space-y-4 sm:space-y-5">
                    @if($hero && $hero->hero_visible && ($hero->hero_headline || $hero->hero_subheadline || $hero->hero_image_path))
                        <article class="rounded-2xl p-5 sm:p-6 text-white text-center shadow-[0_14px_30px_rgba(2,6,23,0.16)] bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600">
                            <h1 class="text-lg sm:text-2xl font-bold leading-tight">{{ $hero->hero_headline ?: 'Premium gadgets from trusted sourcing' }}</h1>
                            @if($hero->hero_subheadline)
                                <p class="text-sm sm:text-base mt-2 text-emerald-50 max-w-3xl mx-auto">{{ $hero->hero_subheadline }}</p>
                            @endif
                        </article>
                    @endif

                    @if($sections->isNotEmpty())
                        <article id="categoryRail" class="premium-card p-3 sm:p-4">
                            <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                                @foreach($sections as $section)
                                    <button type="button"
                                            data-cat-target="{{ $section['id'] }}"
                                            style="{{ $section['id'] === 'premium' ? 'grid-column: 1 / -1;' : '' }}"
                                            class="cat-btn w-full text-left rounded-xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:border-emerald-300">
                                        <span class="block">{{ $section['title'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </article>
                    @endif
                    </section>

            <section id="catalogScroll" class="space-y-4 sm:space-y-5 pt-2">
                    <article class="premium-card-soft grid grid-cols-3 gap-2 p-3 text-center">
                        <div>
                            <div class="text-base sm:text-lg font-bold text-slate-900">24h</div>
                            <div class="text-[11px] text-slate-500">Fast support</div>
                        </div>
                        <div>
                            <div class="text-base sm:text-lg font-bold text-slate-900">100%</div>
                            <div class="text-[11px] text-slate-500">Verified listing</div>
                        </div>
                        <div>
                            <div class="text-base sm:text-lg font-bold text-slate-900">Secure</div>
                            <div class="text-[11px] text-slate-500">WhatsApp flow</div>
                        </div>
                    </article>
 
                    @forelse($sections as $section)
                        <article id="{{ $section['id'] }}" data-section-id="{{ $section['id'] }}" class="scroll-mt-24">
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 mb-2.5">{{ $section['title'] }}</h2>
                            <div class="space-y-2.5">
                                @foreach($section['deals'] as $deal)
                                    @php
                                        $image = ($deal->images ?? collect())->first();
                                        $imageUrl = $image ? storage_asset($image->image_path) : null;
                                        $priceText = preg_replace('/\s+/u', '', trim((string) ($deal->price_display ?? '')));
                                        $priceValue = preg_replace('/^(?:₦|NGN|N)/u', '', $priceText);
                                        $priceValue = $priceValue !== '' ? $priceValue : $priceText;
                                        $specLines = collect(explode("\n", (string) $deal->description))
                                            ->map(fn ($line) => trim($line))
                                            ->filter()
                                            ->map(function ($line) {
                                                [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
                                                return ['key' => trim($key), 'value' => trim($value)];
                                            })
                                            ->filter(fn ($item) => $item['key'] !== '' && $item['value'] !== '' && !in_array(strtolower($item['key']), ['model', 'price', 'cost', 'amount'], true))
                                            ->values();

                                        // Priority chips for list cards:
                                        // 1) RAM  2) ROM/Storage  3) Battery  (then fallback: Processor -> SIM/Connectivity -> other)
                                        $specTypes = [
                                            'ram' => null,
                                            'storage' => null,
                                            'battery' => null,
                                            'processor' => null,
                                            'sim' => null,
                                            'other' => null,
                                        ];

                                        foreach ($specLines as $item) {
                                            $k = strtolower((string) $item['key']);
                                            $v = trim((string) $item['value']);
                                            if ($v === '') {
                                                continue;
                                            }

                                            $vLower = strtolower($v);
                                            $hasMah = str_contains($vLower, 'mah');

                                            if ($specTypes['ram'] === null && str_contains($k, 'ram')) {
                                                $specTypes['ram'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['battery'] === null && (str_contains($k, 'battery') || str_contains($k, 'health') || str_contains($k, 'batt'))) {
                                                $specTypes['battery'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['battery'] === null && $hasMah && str_contains($k, 'capacity')) {
                                                // Battery capacity lines often look like "Battery: ... (9000mAh capacity)".
                                                $specTypes['battery'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['storage'] === null && (str_contains($k, 'rom') || str_contains($k, 'storage'))) {
                                                $specTypes['storage'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['storage'] === null && (str_contains($k, 'internal') || str_contains($k, 'memory'))) {
                                                // "Memory" may sometimes be used for storage; keep as fallback.
                                                $specTypes['storage'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['processor'] === null && (str_contains($k, 'processor') || str_contains($k, 'cpu') || str_contains($k, 'chip'))) {
                                                $specTypes['processor'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['processor'] === null && (str_contains($vLower, 'snapdragon') || str_contains($vLower, 'dimensity') || str_contains($vLower, 'mediatek') || str_contains($vLower, 'qualcomm'))) {
                                                $specTypes['processor'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['sim'] === null && (str_contains($k, 'sim') || str_contains($k, 'connect') || str_contains($k, '5g') || str_contains($k, 'wifi') || str_contains($k, 'dual sim'))) {
                                                $specTypes['sim'] = $v;
                                                continue;
                                            }

                                            if ($specTypes['other'] === null) {
                                                $specTypes['other'] = $v;
                                            }
                                        }

                                        $specChips = collect(['ram', 'storage', 'battery', 'processor', 'sim', 'other'])
                                            ->map(fn ($type) => $specTypes[$type])
                                            ->filter()
                                            ->take(3)
                                            ->values();

                                        $buyUrl = filled($deal->uuid) ? route('deal.whatsapp', ['deal' => $deal->uuid]) : null;
                                        $detailUrl = filled($deal->uuid) ? route('deal.show', ['deal' => $deal->uuid]) : null;
                                    @endphp

                                    <div class="premium-card-soft p-2.5 sm:p-3">
                                        <div class="flex gap-2.5">
                                            @if($detailUrl)
                                                <a href="{{ $detailUrl }}"
                                                   class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg bg-gray-100 overflow-hidden shrink-0 block">
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $deal->title }}" class="w-full h-full object-contain p-1.5">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">No image</div>
                                                    @endif
                                                </a>
                                            @else
                                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $deal->title }}" class="w-full h-full object-contain p-1.5">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">No image</div>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="min-w-0 flex-1">
                                                @if($detailUrl)
                                                    <a href="{{ $detailUrl }}" class="block">
                                                        <h3 class="text-sm sm:text-base font-semibold text-gray-900 leading-tight line-clamp-2">{{ $deal->title }}</h3>
                                                        @if($specChips->isNotEmpty())
                                                            <div class="mt-1.5 flex flex-wrap gap-1">
                                                                @foreach($specChips as $chipValue)
                                                                    <span class="premium-chip whitespace-normal break-words leading-tight">
                                                                        {{ $chipValue }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </a>
                                                @else
                                                    <h3 class="text-sm sm:text-base font-semibold text-gray-900 leading-tight line-clamp-2">{{ $deal->title }}</h3>
                                                    @if($specChips->isNotEmpty())
                                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                                            @foreach($specChips as $chipValue)
                                                                <span class="premium-chip whitespace-normal break-words leading-tight">
                                                                    {{ $chipValue }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif

                                                <div class="mt-2 flex items-center justify-between gap-2">
                                                    <div class="text-emerald-600 font-extrabold text-sm sm:text-base">
                                                        ₦{{ $priceValue !== '' ? $priceValue : '—' }}
                                                    </div>
                                                    @if($buyUrl)
                                                        <a href="{{ $buyUrl }}" class="premium-btn-primary !px-3 !py-1.5 text-[11px] sm:text-xs font-bold rounded-lg">
                                                            WhatsApp Buy
                                                        </a>
                                                    @else
                                                        <span class="inline-flex items-center justify-center rounded-lg bg-gray-200 text-gray-500 text-[11px] sm:text-xs font-semibold px-3 py-1.5">
                                                            Unavailable
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <div class="premium-card p-8 text-center text-slate-500">
                            <p class="font-semibold text-slate-800">No hot deals available right now.</p>
                            <p class="text-sm mt-1">Check back soon for fresh listings.</p>
                        </div>
                    @endforelse
            </section>
        </div>

        <div class="fixed bottom-4 right-4 md:hidden z-50">
            @php $waNumber = preg_replace('/\D/', '', (string) site_setting('whatsapp_number', site_setting('whatsapp_business_number', '2347084117779'))); @endphp
            <a href="https://wa.me/{{ $waNumber ?: '2347084117779' }}" class="flex items-center justify-center w-14 h-14 text-white bg-gradient-to-r from-emerald-600 to-sky-600 hover:from-emerald-700 hover:to-sky-700 rounded-full shadow-lg transition" aria-label="WhatsApp">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/>
                </svg>
            </a>
        </div>

        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
            .cat-btn.active {
                background: linear-gradient(135deg, #ecfdf5 0%, #eff6ff 100%);
                color: #0f766e;
                border: 1px solid #7dd3fc;
                box-shadow: 0 10px 18px -12px rgba(14, 165, 233, 0.45);
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const buttons = document.querySelectorAll('.cat-btn');
                const sections = document.querySelectorAll('[data-section-id]');

                const setActive = (id) => {
                    buttons.forEach((btn) => {
                        btn.classList.toggle('active', btn.dataset.catTarget === id);
                    });
                };

                buttons.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.catTarget;
                        const target = document.getElementById(id);
                        if (target) {
                            const y = target.getBoundingClientRect().top + window.scrollY - 220;
                            window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
                            setActive(id);
                        }
                    });
                });

                if ('IntersectionObserver' in window && sections.length > 0) {
                    const observer = new IntersectionObserver((entries) => {
                        const visible = entries
                            .filter((entry) => entry.isIntersecting)
                            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
                        if (visible) {
                            setActive(visible.target.dataset.sectionId);
                        }
                    }, { threshold: [0.3, 0.5, 0.7], rootMargin: '-180px 0px -40% 0px' });

                    sections.forEach((section) => observer.observe(section));
                }
            });
        </script>

    </div>
</x-app-layout>
