<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-green-800">Variants: {{ $model->brand->name }} → {{ $model->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Price combinations (Storage + Appearance + Function). To add or edit variants, use Edit model.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.phones.models.index', $model->brand) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium">← Models</a>
                    <a href="{{ route('admin.phones.models.edit', $model) }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium whitespace-nowrap">Edit model & variants</a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                 class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Spec combination</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min CNY</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max CNY</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($variants as $v)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @foreach($v->specValues as $sv)
                                    <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-800 mr-1">{{ $sv->spec->name ?? '' }}: {{ $sv->value }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($v->min_price_cny, 2) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($v->max_price_cny, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('admin.phones.variants.destroy', $v) }}" method="POST" class="inline" onsubmit="return confirm('Delete this variant?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No variants yet. Use “Edit model & variants” above to add price combinations.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
