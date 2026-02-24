<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white sticky top-0 z-10 shadow-sm px-4 py-3 flex items-center gap-3">
            <a href="{{ route('public.phones.brand', ['brandUuid' => $brand->uuid]) }}" class="text-blue-600 font-semibold">Back</a>
            <h1 class="text-lg font-bold text-gray-800 line-clamp-1">{{ $device->name }}</h1>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-4"
             x-data="singleDeviceConfigurator(@js($deviceRuleOptions))">
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex items-center gap-3">
                @php($brandImage = $brand->representative_image ?: $brand->image_path)
                @if($brandImage)
                    <img src="{{ asset('storage/' . $brandImage) }}" alt="{{ $brand->name }}" class="h-12 w-12 rounded-xl object-contain bg-gray-100 p-1">
                @else
                    <div class="h-12 w-12 rounded-xl bg-gray-100"></div>
                @endif
                <div>
                    <div class="text-sm text-gray-500">{{ $brand->name }}</div>
                    <div class="text-base font-bold text-gray-800">{{ $device->name }}</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                @if(session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="spec-form" method="POST" action="{{ route('public.phones.whatsapp', ['deviceUuid' => $device->uuid]) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Memory</label>
                        <select x-model="selectedMemoryId" @change="onMemoryChange"
                                name="memory_id"
                                class="w-full border border-gray-300 rounded-xl p-3 text-base"
                                required>
                            <option value="">Select Memory</option>
                            <template x-for="memory in availableMemories" :key="memory.id">
                                <option :value="String(memory.id)" x-text="memory.label"></option>
                            </template>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Function Grade</label>
                        <select x-model="selectedFunctionalityId" @change="onFunctionalityChange"
                                name="functionality_grade_id"
                                :disabled="!selectedMemoryId"
                                class="w-full border border-gray-300 rounded-xl p-3 text-base disabled:bg-gray-100 disabled:text-gray-400"
                                required>
                            <option value="">Select Grade</option>
                            <template x-for="grade in availableFunctionalities" :key="grade.id">
                                <option :value="String(grade.id)" x-text="grade.label"></option>
                            </template>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Appearance</label>
                        <select x-model="selectedAppearanceId" @change="syncSelectedRule"
                                name="appearance_grade_id"
                                :disabled="!selectedFunctionalityId"
                                class="w-full border border-gray-300 rounded-xl p-3 text-base disabled:bg-gray-100 disabled:text-gray-400"
                                required>
                            <option value="">Select Appearance</option>
                            <template x-for="appearance in availableAppearances" :key="appearance.id">
                                <option :value="String(appearance.id)" x-text="appearance.label"></option>
                            </template>
                        </select>
                    </div>

                    <div class="mb-4 rounded-xl border border-green-100 bg-green-50 p-4" x-show="selectedRule">
                        <div class="text-sm font-semibold text-green-700">Estimated Price Range</div>
                        <div class="text-2xl md:text-3xl font-extrabold text-green-700 mt-1">
                            NGN <span x-text="formatNaira(selectedRule ? selectedRule.min_price_ngn : 0)"></span>
                            -
                            NGN <span x-text="formatNaira(selectedRule ? selectedRule.max_price_ngn : 0)"></span>
                        </div>
                    </div>

                    <button type="submit" :disabled="!selectedRule"
                            class="w-full min-h-14 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold text-base rounded-xl transition">
                        WhatsApp to Buy
                    </button>
                </form>

                <a href="{{ route('public.phones.request.form', ['brandUuid' => $brand->uuid]) }}"
                   class="mt-3 w-full min-h-12 border border-blue-200 text-blue-700 font-semibold rounded-xl flex items-center justify-center">
                    Request Another Model
                </a>
            </div>
        </div>

        <script>
            function singleDeviceConfigurator(ruleOptions) {
                return {
                    rules: ruleOptions || [],
                    selectedMemoryId: '',
                    selectedFunctionalityId: '',
                    selectedAppearanceId: '',
                    selectedRule: null,
                    availableMemories: [],
                    availableFunctionalities: [],
                    availableAppearances: [],
                    init() {
                        this.availableMemories = this.uniqueBy(this.rules, 'memory_id', 'memory_label');
                    },
                    onMemoryChange() {
                        this.selectedFunctionalityId = '';
                        this.selectedAppearanceId = '';
                        this.selectedRule = null;
                        this.availableFunctionalities = this.uniqueBy(this.rules, 'functionality_grade_id', 'functionality_label', (rule) => {
                            return String(rule.memory_id) === String(this.selectedMemoryId);
                        });
                        this.availableAppearances = [];
                    },
                    onFunctionalityChange() {
                        this.selectedAppearanceId = '';
                        this.selectedRule = null;
                        this.availableAppearances = this.uniqueBy(this.rules, 'appearance_grade_id', 'appearance_label', (rule) => {
                            return String(rule.memory_id) === String(this.selectedMemoryId) &&
                                String(rule.functionality_grade_id) === String(this.selectedFunctionalityId);
                        });
                    },
                    syncSelectedRule() {
                        this.selectedRule = this.rules.find((rule) => {
                            return String(rule.memory_id) === String(this.selectedMemoryId) &&
                                String(rule.functionality_grade_id) === String(this.selectedFunctionalityId) &&
                                String(rule.appearance_grade_id) === String(this.selectedAppearanceId);
                        }) || null;
                    },
                    uniqueBy(rules, idKey, labelKey, predicate = null) {
                        const seen = new Set();
                        const result = [];

                        for (const rule of (rules || [])) {
                            if (predicate && !predicate(rule)) {
                                continue;
                            }

                            const id = String(rule[idKey]);
                            if (seen.has(id)) {
                                continue;
                            }

                            seen.add(id);
                            result.push({
                                id: rule[idKey],
                                label: rule[labelKey],
                            });
                        }

                        return result;
                    },
                    formatNaira(value) {
                        return Number(value || 0).toLocaleString();
                    },
                };
            }
        </script>
    </div>
</x-app-layout>



