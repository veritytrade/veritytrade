<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">Ingested Products</h2>
                    <p class="text-sm text-gray-500 mt-1">Review, edit, and approve listings from pipeline ingestion.</p>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="" {{ $statusFilter === '' ? 'selected' : '' }}>All statuses</option>
                        <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="archived" {{ $statusFilter === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    <button type="submit" class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium">Filter</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hidden md:block">
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

        <div class="space-y-3 md:hidden">
            @forelse($products as $product)
                <article class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $product->title }}</h3>
                    <p class="text-xs text-gray-600 mt-1">₦{{ number_format((int) $product->price_ngn) }} • {{ $product->images_count }} image(s)</p>
                    <p class="text-xs text-gray-500 mt-1 break-all">{{ $product->source_site }} / {{ $product->source_item_id }}</p>
                    <div class="mt-2">
                        <span class="px-2 py-1 rounded-full text-[11px] font-semibold {{ $product->status === 'active' ? 'bg-green-100 text-green-700' : ($product->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ strtoupper($product->status) }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <a href="{{ route('admin.products.show', $product) }}" class="flex-1 text-center px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium">Preview</a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="flex-1 text-center px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-xs font-medium">Edit</a>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-lg border border-gray-200 p-5 text-center text-sm text-gray-500">
                    No ingested products yet.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</x-admin-layout>
