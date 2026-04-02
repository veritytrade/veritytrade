<x-admin-layout>
    <div class="max-w-4xl mx-auto p-4 md:p-6 space-y-4">
        <nav class="text-xs text-gray-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-green-700">Dashboard</a>
            <span class="mx-1">/</span>
            <a href="{{ route('admin.products.index') }}" class="hover:text-green-700">Products</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700 font-medium truncate inline-block align-bottom max-w-[200px]">{{ $product->title }}</span>
        </nav>
        <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
            <p class="text-xs text-gray-500 mb-3">
                <strong class="text-gray-700">Products ↔ Hot Deals:</strong>
                Use <strong>Approve to Hot Deal</strong> to create or refresh a deal from this row (text + images are copied; the deal is linked via <code class="text-[11px] bg-gray-100 px-1 rounded">source_product_id</code>).
                Use <strong>Deals → Create Hot Deal</strong> for a standalone deal with no product link.
            </p>
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                <h2 class="text-xl font-bold text-gray-900">{{ $product->title }}</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium">Back to Products</a>
                    <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium">Edit</a>
                    @if($canApproveToHotDeal ?? false)
                        <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium">Approve to Hot Deal</button>
                        </form>
                    @elseif($product->sourceDeal)
                        <a href="{{ route('admin.deals.edit', ['deal' => $product->sourceDeal, 'from_product' => $product->id]) }}" class="px-3 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium">Edit Hot Deal</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div>
                @if(isset($previousProduct) && $previousProduct)
                    <a href="{{ route('admin.products.show', $previousProduct) }}" class="text-sm text-blue-600 hover:text-blue-800">← Previous Product</a>
                @endif
            </div>
            <div>
                @if(isset($nextProduct) && $nextProduct)
                    <a href="{{ route('admin.products.show', $nextProduct) }}" class="text-sm text-blue-600 hover:text-blue-800">Next Product →</a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Hot Deal body (preview)</h3>
                <p class="text-xs text-gray-500 mb-2">This is the same text used when you approve to Hot Deal (specs, notes, extra description, and price line).</p>
                <p class="text-sm text-gray-700 whitespace-pre-line font-mono bg-gray-50 border border-gray-100 rounded-lg p-3">{{ $dealBodyPreview ?? '' }}</p>
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
