<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-blue-700">Model Management</h2>
            <p class="text-sm text-gray-500 mt-1">Manage models by brand (no series) or by specific series.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-base font-bold text-gray-800 mb-3">Brand-Level Models</h3>
                <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    @forelse($brands as $brand)
                        <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $brand->name }}</div>
                                <div class="text-xs text-gray-500">{{ optional($brand->category)->name ?? 'No Category' }}</div>
                            </div>
                            <a href="{{ route('admin.brand-models.index', $brand) }}"
                               class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg text-xs font-medium">
                                Manage
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No brands found.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-base font-bold text-gray-800 mb-3">Series Models</h3>
                <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    @forelse($series as $item)
                        <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $item->name }}</div>
                                <div class="text-xs text-gray-500">{{ optional($item->brand)->name ?? 'No Brand' }}</div>
                            </div>
                            <a href="{{ route('admin.models.index', $item) }}"
                               class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-medium">
                                Manage
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No series found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

