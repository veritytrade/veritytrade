<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-blue-700">Feature Flags & Settings</h2>
            <p class="text-sm text-gray-500 mt-1">Control platform behavior without deploying code.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="space-y-4">
            @foreach($flags as $flag)
                <form method="POST" action="{{ route('admin.feature-flags.update', $flag) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                        <div class="md:col-span-4">
                            <div class="font-semibold text-gray-800">{{ $flag->key }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $flag->description ?: 'No description' }}</div>
                            <div class="text-[11px] text-gray-400 mt-1">Group: {{ $flag->group ?: 'general' }}</div>
                        </div>

                        <div class="md:col-span-5">
                            <input type="text" name="value" value="{{ $flag->value }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>

                        <div class="md:col-span-1 flex justify-start md:justify-center">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_active" value="1" {{ $flag->is_active ? 'checked' : '' }}>
                                Active
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Save</button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-admin-layout>
