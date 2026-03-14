<x-app-layout>
    <div class="min-h-screen" x-data="phoneModelPage()">
        <div class="max-w-2xl mx-auto px-4 py-6 pb-24">
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm text-gray-500 md:space-x-2">
                    <li><a href="{{ route('phones.index') }}" class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">Phones</a></li>
                    <li class="flex items-center"><span class="mx-1">/</span><a href="{{ route('phones.brand', $brand->slug) }}" class="text-green-600 hover:text-green-700 font-medium">{{ $brand->name }}</a></li>
                    <li class="flex items-center"><span class="mx-1">/</span><span class="text-gray-700 font-medium">{{ $model->name }}</span></li>
                </ol>
            </nav>

            <div class="block p-0 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6" x-data="{ currentImage: '{{ $model->images->isNotEmpty() ? storage_asset($model->images->first()->path) : ($model->image ? storage_asset($model->image) : '') }}' }">
                <div class="aspect-square bg-white p-4 flex items-center justify-center">
                    @if($model->images->isNotEmpty() || $model->image)
                        <img :src="currentImage" alt="{{ $model->name }}" class="max-w-full max-h-full object-contain" id="model-main-image">
                    @else
                        <div class="w-full h-full min-h-[200px] bg-gray-100 rounded-xl flex items-center justify-center text-gray-400">No image</div>
                    @endif
                </div>
                @if($model->images->isNotEmpty() || $model->image)
                    @php
                        $galleryImages = $model->images->isNotEmpty()
                            ? $model->images->map(fn($i) => storage_asset($i->path))->values()
                            : collect([storage_asset($model->image)]);
                    @endphp
                    <div class="flex flex-wrap gap-2 p-3 border-t border-gray-100 bg-gray-50">
                        @foreach($galleryImages as $idx => $imgUrl)
                            <button type="button"
                                    @click="currentImage = '{{ $imgUrl }}'"
                                    class="w-14 h-14 rounded-lg border-2 overflow-hidden flex-shrink-0 transition focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                    :class="currentImage === '{{ $imgUrl }}' ? 'border-green-600' : 'border-gray-200 hover:border-gray-300'">
                                <img src="{{ $imgUrl }}" alt="" class="w-full h-full object-contain">
                            </button>
                        @endforeach
                    </div>
                @endif
                <div class="p-4 border-t border-gray-100">
                    <h1 class="text-xl font-bold text-gray-900">{{ $model->name }}</h1>
                </div>
            </div>

            {{-- Spec selection: Storage (primary), then Appearance, then Function --}}
            <div class="block p-4 md:p-6 bg-white border border-gray-200 rounded-xl shadow-sm mb-6 space-y-6">
                @foreach($specs as $spec)
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">{{ $spec->name }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($spec->values as $val)
                                @if($spec->name === 'Storage')
                                    <button type="button"
                                            @click="selectStorage({{ $val->id }})"
                                            :class="selectedStorageId === {{ $val->id }} ? 'bg-green-600 text-white ring-2 ring-green-600' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                                            class="px-4 py-2.5 rounded-xl font-medium text-sm transition">
                                        {{ $val->value }}
                                    </button>
                                @elseif($spec->name === 'Appearance')
                                    <button type="button"
                                            :disabled="!isAppearanceAvailable({{ $val->id }})"
                                            @click="selectAppearance({{ $val->id }})"
                                            :class="isAppearanceAvailable({{ $val->id }}) ? (selectedAppearanceId === {{ $val->id }} ? 'bg-green-600 text-white ring-2 ring-green-600' : 'bg-gray-100 text-gray-800 hover:bg-gray-200') : 'bg-gray-50 text-gray-400 cursor-not-allowed opacity-60'"
                                            class="px-4 py-2.5 rounded-xl font-medium text-sm transition disabled:pointer-events-none">
                                        {{ $val->value }}
                                    </button>
                                @else
                                    <button type="button"
                                            :disabled="!isFunctionAvailable({{ $val->id }})"
                                            @click="selectFunction({{ $val->id }})"
                                            :class="isFunctionAvailable({{ $val->id }}) ? (selectedFunctionId === {{ $val->id }} ? 'bg-green-600 text-white ring-2 ring-green-600' : 'bg-gray-100 text-gray-800 hover:bg-gray-200') : 'bg-gray-50 text-gray-400 cursor-not-allowed opacity-60'"
                                            class="px-4 py-2.5 rounded-xl font-medium text-sm transition disabled:pointer-events-none">
                                        {{ $val->value }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <p class="text-sm text-gray-500 pt-2">
                    <button type="button" @click="isModalOpen = true" class="text-green-600 hover:text-green-800 font-medium underline">
                        What do Appearance &amp; Function grades mean?
                    </button>
                </p>
            </div>

            {{-- Price (only when full valid combination) --}}
            <div x-show="selectedVariant" x-cloak
                 class="block p-4 mb-6 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm font-semibold text-green-800 mb-1">Price range (NGN)</p>
                <p class="text-2xl font-bold text-green-800" x-text="selectedVariant ? '₦' + Number(selectedVariant.min_ngn).toLocaleString() + ' - ₦' + Number(selectedVariant.max_ngn).toLocaleString() : ''"></p>
            </div>

            {{-- Request button (always visible; disabled until variant selected) --}}
            <a href="https://wa.me/2347084117779"
               :href="selectedVariant ? 'https://wa.me/2347084117779?text=' + encodeURIComponent(requestMessage()) : '#'"
               :class="selectedVariant ? 'bg-green-600 hover:bg-green-700 cursor-pointer focus:ring-4 focus:ring-green-300' : 'bg-gray-300 text-gray-500 cursor-not-allowed pointer-events-none'"
               class="block w-full min-h-14 rounded-xl font-bold text-white text-center flex items-center justify-center transition">
                Request via WhatsApp
            </a>
        </div>

        {{-- Grading explanation modal (static content) --}}
        <div x-show="isModalOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="isModalOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5);">
            <div @click.self="isModalOpen = false"
                 class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col border border-gray-200">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 shrink-0">
                    <h2 class="text-lg font-bold text-gray-900">Grade meanings</h2>
                    <button type="button" @click="isModalOpen = false" class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 focus:ring-2 focus:ring-gray-200" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-4 space-y-6 text-sm">
                    <section>
                        <h3 class="font-bold text-gray-900 mb-2">Appearance Grade (Cosmetic Condition)</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">%</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Meaning</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr><td class="px-3 py-2">99%</td><td class="px-3 py-2">Almost new, minimal signs of use</td></tr>
                                    <tr><td class="px-3 py-2">95%</td><td class="px-3 py-2">Light usage marks, no cracks</td></tr>
                                    <tr><td class="px-3 py-2">90%</td><td class="px-3 py-2">Visible wear, small scratches</td></tr>
                                    <tr><td class="px-3 py-2">80%</td><td class="px-3 py-2">Noticeable wear, economically priced</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 text-gray-600">Appearance refers to cosmetic condition only. Does not affect functionality.</p>
                    </section>
                    <section>
                        <h3 class="font-bold text-gray-900 mb-2">Function Grade (Device Performance &amp; Repair Status)</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li><strong>S Grade</strong> — Fully functional, no repairs or replacements, battery 95%+, warranty &gt;100 days</li>
                            <li><strong>A Grade</strong> — Fully functional, no repairs, battery 85%+</li>
                            <li><strong>B Grade</strong> — Minor functional flaws OR new battery replacement, no major repair, battery 80%+ or replaced battery</li>
                            <li><strong>C Grade</strong> — Functional flaws present, possible screen/battery replacement, core functions usable</li>
                        </ul>
                        <p class="mt-2 text-gray-600">Function grade refers to internal performance and repair history.</p>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        function phoneModelPage() {
            const specs = @json($specs->map(fn($s) => ['name' => $s->name, 'values' => $s->values->pluck('id')])->keyBy('name'));
            const variants = @json($variants);

            return {
                isModalOpen: false,
                selectedStorageId: null,
                selectedAppearanceId: null,
                selectedFunctionId: null,
                variants,

                get variantsAfterStorage() {
                    if (!this.selectedStorageId) return [];
                    return this.variants.filter(v => v.storage_id === this.selectedStorageId);
                },
                get variantsAfterStorageAppearance() {
                    if (!this.selectedAppearanceId) return [];
                    return this.variantsAfterStorage.filter(v => v.appearance_id === this.selectedAppearanceId);
                },
                get selectedVariant() {
                    if (!this.selectedStorageId || !this.selectedAppearanceId || !this.selectedFunctionId) return null;
                    return this.variantsAfterStorageAppearance.find(v => v.function_id === this.selectedFunctionId) || null;
                },

                selectStorage(id) {
                    this.selectedStorageId = id;
                    this.selectedAppearanceId = null;
                    this.selectedFunctionId = null;
                },
                selectAppearance(id) {
                    this.selectedAppearanceId = id;
                    this.selectedFunctionId = null;
                },
                selectFunction(id) {
                    this.selectedFunctionId = id;
                },

                isAppearanceAvailable(valueId) {
                    if (!this.selectedStorageId) return false;
                    return this.variantsAfterStorage.some(v => v.appearance_id === valueId);
                },
                isFunctionAvailable(valueId) {
                    if (!this.selectedAppearanceId) return false;
                    return this.variantsAfterStorageAppearance.some(v => v.function_id === valueId);
                },

                requestMessage() {
                    if (!this.selectedVariant) return '';
                    return 'Hello, I\'m interested in {{ $model->name }} ({{ $brand->name }}). Price range: ₦' + Number(this.selectedVariant.min_ngn).toLocaleString() + ' - ₦' + Number(this.selectedVariant.max_ngn).toLocaleString() + '. Please confirm availability.';
                }
            };
        }
    </script>
</x-app-layout>
