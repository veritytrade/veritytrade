<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">Products</h2>
                    <p class="text-sm text-gray-500 mt-1">Review, edit, publish, archive, or delete ingested listings.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="p-3 text-left font-semibold">Title</th>
                            <th class="p-3 text-left font-semibold">Price</th>
                            <th class="p-3 text-left font-semibold">Status</th>
                            <th class="p-3 text-left font-semibold">Source</th>
                            <th class="p-3 text-left font-semibold">Images</th>
                            <th class="p-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 text-gray-800 font-medium max-w-xs truncate" title="{{ $product->title }}">{{ $product->title }}</td>
                                <td class="p-3 text-gray-700">₦{{ number_format((int) $product->price_ngn) }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $product->status === 'active' ? 'bg-green-100 text-green-700' : ($product->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ strtoupper($product->status) }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-600 max-w-xs truncate" title="{{ $product->source_site }} / {{ $product->source_item_id }}">{{ $product->source_site }} / {{ $product->source_item_id }}</td>
                                <td class="p-3 text-gray-700">{{ $product->images_count }}</td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.products.show', $product) }}" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium">Preview</a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-xs font-medium">Edit</a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product and all media? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">No ingested products yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</x-admin-layout>
