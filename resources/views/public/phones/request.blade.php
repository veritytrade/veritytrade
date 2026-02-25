<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white sticky top-0 z-10 shadow-sm px-4 py-3 flex items-center gap-3">
            <a href="{{ route('public.phones.brand', ['brandUuid' => $brand->uuid]) }}" class="text-blue-600 font-semibold">Back</a>
            <h1 class="text-lg font-bold text-gray-800">{{ $brand->name }}</h1>
        </div>

        <div class="max-w-xl mx-auto px-4 py-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-lg font-bold text-blue-600 mb-2">Model Not Listed Yet</h2>
                <p class="text-sm text-gray-600 mb-4">Send your preferred model and contact number. We will confirm availability and pricing.</p>

                <form method="POST" action="{{ route('public.phones.request', ['brandUuid' => $brand->uuid]) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Model</label>
                        <input type="text" name="manual_model_name" value="{{ old('manual_model_name') }}" required
                               class="w-full border border-gray-300 rounded-xl p-3 text-base" placeholder="e.g. Redmi Note 13 Pro 256GB">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Memory</label>
                        <select name="memory_id" required class="w-full border border-gray-300 rounded-xl p-3 text-base">
                            <option value="">Select Memory</option>
                            @foreach($memories as $memory)
                                <option value="{{ $memory->id }}">{{ $memory->size_gb }}GB</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Function Grade</label>
                        <select name="functionality_grade_id" required class="w-full border border-gray-300 rounded-xl p-3 text-base">
                            <option value="">Select Function Grade</option>
                            @foreach($functionalities as $functionality)
                                <option value="{{ $functionality->id }}">{{ $functionality->grade }} Grade</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Appearance</label>
                        <select name="appearance_grade_id" required class="w-full border border-gray-300 rounded-xl p-3 text-base">
                            <option value="">Select Appearance</option>
                            @foreach($appearances as $appearance)
                                <option value="{{ $appearance->id }}">{{ $appearance->percentage }}%</option>
                            @endforeach
                        </select>
                    </div>

                    @foreach($specGroups as $group)
                        @foreach($group->specs as $spec)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $spec->name }} @if($spec->is_required)<span class="text-red-500">*</span>@endif</label>
                                @if($spec->input_type === 'dropdown')
                                    <select name="request_specs[{{ $spec->id }}]" class="w-full border border-gray-300 rounded-xl p-3 text-base" {{ $spec->is_required ? 'required' : '' }}>
                                        <option value="">Select {{ $spec->name }}</option>
                                        @foreach($spec->values as $value)
                                            <option value="{{ $value->id }}">{{ $value->value }}</option>
                                        @endforeach
                                    </select>
                                @elseif($spec->input_type === 'number')
                                    <input type="number" name="request_specs[{{ $spec->id }}]" class="w-full border border-gray-300 rounded-xl p-3 text-base" {{ $spec->is_required ? 'required' : '' }}>
                                @else
                                    <input type="text" name="request_specs[{{ $spec->id }}]" class="w-full border border-gray-300 rounded-xl p-3 text-base" {{ $spec->is_required ? 'required' : '' }}>
                                @endif
                            </div>
                        @endforeach
                    @endforeach

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Your Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                               class="w-full border border-gray-300 rounded-xl p-3 text-base" placeholder="e.g. 08012345678">
                    </div>

                    <button type="submit" class="w-full min-h-14 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 transition">
                        Submit Request on WhatsApp
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

