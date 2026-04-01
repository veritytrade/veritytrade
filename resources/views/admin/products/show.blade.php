<x-admin-layout>
    <div class="max-w-4xl mx-auto p-4 md:p-6 space-y-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                <h2 class="text-xl font-bold text-gray-900">{{ $product->title }}</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium">Edit</a>
                    @if($product->status !== 'active')
                        <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium">Approve to Hot Deal</button>
                        </form>
                    @endif
                    @if($product->status !== 'archived')
                        <form method="POST" action="{{ route('admin.products.archive', $product) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Archive</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $product->description_en ?: 'No description yet.' }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-medium text-gray-700 mb-2">Source URL (private)</p>
                @if(filled($product->source_url_private))
                    <div class="overflow-x-auto rounded border border-gray-200 bg-white p-2">
                        <code class="text-[11px] text-gray-700 whitespace-nowrap">{{ $product->source_url_private }}</code>
                    </div>
                    <div class="mt-2">
                        <a href="{{ $product->source_url_private }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium">
                            Open Source Listing
                        </a>
                    </div>
                @else
                    <p class="text-xs text-gray-500">No source URL available.</p>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
