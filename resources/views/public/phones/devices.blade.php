<x-app-layout>
    @php
        $deviceOptions = $devices->map(fn ($device) => [
            'id' => $device->id,
            'uuid' => $device->uuid,
            'name' => $device->name,
        ])->values();
    @endphp

    <div class="min-h-screen bg-gray-50">
        <div class="bg-white sticky top-0 z-10 shadow-sm px-4 py-3 flex items-center gap-3">
            <a href="{{ route('public.phones.brands') }}" class="text-blue-600 font-semibold">Back</a>
            <h1 class="text-lg font-bold text-gray-800">{{ $brand->name }} Pricing</h1>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-4"
             x-data="directPhoneConfigurator(@js($deviceOptions), @js($ruleOptions))">
            <script>
                const phoneWhatsappTemplate = @js(route('public.phones.whatsapp', ['deviceUuid' => '__DEVICE_UUID__']));
            </script>
            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 mb-4">
                <h2 class="text-lg md:text-xl font-bold text-blue-600 mb-4">Select Your Exact Spec</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Model</label>
                        <select x-model="selectedModelId" @change="onModelChange"
                                class="w-full border border-gray-300 rounded-xl p-3 text-base">
                            <option value="">Select Model</option>
                            <template x-for="model in deviceOptions" :key="model.id">
                                <option :value="String(model.id)" x-text="model.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Memory</label>
                        <select x-model="selectedMemoryId" @change="onMemoryChange"
                                :disabled="!selectedModelId"
                                class="w-full border border-gray-300 rounded-xl p-3 text-base disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">Select Memory</option>
                            <template x-for="memory in availableMemories" :key="memory.id">
                                <option :value="String(memory.id)" x-text="memory.label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Function Grade</label>
                        <select x-model="selectedFunctionalityId" @change="onFunctionalityChange"
                                :disabled="!selectedMemoryId"
                                class="w-full border border-gray-300 rounded-xl p-3 text-base disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">Select Function Grade</option>
                            <template x-for="grade in availableFunctionalities" :key="grade.id">
                                <option :value="String(grade.id)" x-text="grade.label"></option>
                            </template>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Appearance</label>
                        <select x-model="selectedAppearanceId" @change="syncSelectedRule"
                                :disabled="!selectedFunctionalityId"
                                class="w-full border border-gray-300 rounded-xl p-3 text-base disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">Select Appearance</option>
                            <template x-for="appearance in availableAppearances" :key="appearance.id">
                                <option :value="String(appearance.id)" x-text="appearance.label"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-green-100 bg-green-50 p-4" x-show="selectedRule">
                    <div class="text-sm font-semibold text-green-700">Estimated Price Range</div>
                    <div class="text-2xl md:text-3xl font-extrabold text-green-700 mt-1">
                        NGN <span x-text="formatNaira(selectedRule ? selectedRule.min_price_ngn : 0)"></span>
                        -
                        NGN <span x-text="formatNaira(selectedRule ? selectedRule.max_price_ngn : 0)"></span>
                    </div>
                </div>

                <form method="POST" :action="whatsappAction" class="mt-4">
                    @csrf
                    <input type="hidden" name="memory_id" :value="selectedMemoryId">
                    <input type="hidden" name="functionality_grade_id" :value="selectedFunctionalityId">
                    <input type="hidden" name="appearance_grade_id" :value="selectedAppearanceId">

                    <button type="submit"
                            :disabled="!selectedRule"
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
    </div>

    <script>
        function directPhoneConfigurator(deviceOptions, ruleOptions) {
            return {
                deviceOptions: deviceOptions || [],
                ruleOptions: ruleOptions || {},
                selectedModelId: '',
                selectedMemoryId: '',
                selectedFunctionalityId: '',
                selectedAppearanceId: '',
                selectedRule: null,
                availableMemories: [],
                availableFunctionalities: [],
                availableAppearances: [],
                whatsappAction: '#',
                onModelChange() {
                    const model = this.deviceOptions.find((item) => String(item.id) === String(this.selectedModelId));
                    this.whatsappAction = model && model.uuid ? phoneWhatsappTemplate.replace('__DEVICE_UUID__', model.uuid) : '#';
                    this.selectedMemoryId = '';
                    this.selectedFunctionalityId = '';
                    this.selectedAppearanceId = '';
                    this.selectedRule = null;
                    this.availableFunctionalities = [];
                    this.availableAppearances = [];
                    this.availableMemories = this.uniqueBy(this.filteredRules(), 'memory_id', 'memory_label');
                },
                onMemoryChange() {
                    this.selectedFunctionalityId = '';
                    this.selectedAppearanceId = '';
                    this.selectedRule = null;
                    this.availableFunctionalities = this.uniqueBy(this.filteredRules(), 'functionality_grade_id', 'functionality_label', (rule) => {
                        return String(rule.memory_id) === String(this.selectedMemoryId);
                    });
                    this.availableAppearances = [];
                },
                onFunctionalityChange() {
                    this.selectedAppearanceId = '';
                    this.selectedRule = null;
                    this.availableAppearances = this.uniqueBy(this.filteredRules(), 'appearance_grade_id', 'appearance_label', (rule) => {
                        return String(rule.memory_id) === String(this.selectedMemoryId) &&
                            String(rule.functionality_grade_id) === String(this.selectedFunctionalityId);
                    });
                },
                syncSelectedRule() {
                    this.selectedRule = this.filteredRules().find((rule) => {
                        return String(rule.memory_id) === String(this.selectedMemoryId) &&
                            String(rule.functionality_grade_id) === String(this.selectedFunctionalityId) &&
                            String(rule.appearance_grade_id) === String(this.selectedAppearanceId);
                    }) || null;
                },
                filteredRules() {
                    if (!this.selectedModelId) {
                        return [];
                    }

                    return this.ruleOptions[this.selectedModelId] || [];
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
</x-app-layout>



