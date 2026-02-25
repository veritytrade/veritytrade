<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white sticky top-0 z-10 shadow-sm px-4 py-3 flex items-center gap-3">
            <a href="{{ route('home') . '#' . \Illuminate\Support\Str::slug($category->name) }}" class="text-blue-600 font-semibold">Back</a>
            <h1 class="text-lg font-bold text-gray-800">{{ $category->name }} Request</h1>
        </div>

        <div class="max-w-xl mx-auto px-4 py-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-lg font-bold text-blue-600 mb-2">Request {{ $category->name }} Item</h2>
                <p class="text-sm text-gray-600 mb-4">Select available specs and submit request via WhatsApp.</p>

                <form method="POST" action="{{ route('public.categories.request.submit', ['categorySlug' => \Illuminate\Support\Str::slug($category->name)]) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Brand (optional)</label>
                        <select name="brand_id" class="w-full border border-gray-300 rounded-xl p-3 text-base">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ (string) old('brand_id', request('brand_id')) === (string) $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Model Name</label>
                        <input type="text" name="manual_model_name" required class="w-full border border-gray-300 rounded-xl p-3 text-base" placeholder="e.g. EliteBook 840 G8">
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
                        <input type="text" name="phone_number" required class="w-full border border-gray-300 rounded-xl p-3 text-base" placeholder="e.g. 08012345678">
                    </div>

                    <button type="submit" class="w-full min-h-14 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 transition">
                        Submit Request on WhatsApp
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
