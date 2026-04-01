<x-admin-layout>
    <div class="max-w-5xl mx-auto p-4 md:p-6 space-y-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $product->title }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Source: {{ $product->source_site }} / {{ $product->source_item_id }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium">Edit</a>
                    @if($product->status !== 'active')
                        <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium">Approve</button>
                        </form>
                    @endif
                    @if($product->status !== 'archived')
                        <form method="POST" action="{{ route('admin.products.archive', $product) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Archive</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product and all media? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 rounded-lg bg-red-900 hover:bg-black text-white text-sm font-medium">Delete</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200 p-4 md:p-6">
                <h3 class="font-semibold text-gray-900 mb-2">Listing format preview</h3>
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                    <p class="text-sm font-semibold text-gray-900">{{ $product->title }}</p>
                    <div class="mt-2 space-y-1">
                        @if(is_array($product->specs_json) && count($product->specs_json))
                            @foreach($product->specs_json as $k => $v)
                                <p class="text-xs text-gray-700"><span class="font-medium">{{ $k }}:</span> {{ $v }}</p>
                            @endforeach
                        @else
                            <p class="text-xs text-gray-500">No specs provided.</p>
                        @endif
                    </div>
                    @if(filled($product->condition_notes))
                        <div class="mt-3 pt-2 border-t border-blue-100">
                            <p class="text-xs font-medium text-gray-800">Condition Notes</p>
                            <p class="text-xs text-gray-700 whitespace-pre-line mt-1">{{ $product->condition_notes }}</p>
                        </div>
                    @endif
                    <p class="mt-3 text-sm font-bold text-green-700">₦{{ number_format((int) $product->price_ngn) }}</p>
                </div>

                <h3 class="font-semibold text-gray-900 mt-6 mb-2">Description (editable)</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $product->description_en ?: 'No description yet.' }}</p>

                <h3 class="font-semibold text-gray-900 mt-6 mb-2">Specs JSON (raw)</h3>
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded p-3 overflow-x-auto">{{ json_encode($product->specs_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: 'null' }}</pre>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 space-y-3">
                <h3 class="font-semibold text-gray-900">Private source details</h3>
                <ul class="text-sm space-y-2 text-gray-700">
                    <li><span class="font-medium">Status:</span> {{ $product->status }}</li>
                    <li><span class="font-medium">Price:</span> ₦{{ number_format((int) $product->price_ngn) }}</li>
                    <li><span class="font-medium">Stock:</span> {{ (int) $product->stock }}</li>
                    <li><span class="font-medium">Source site:</span> {{ $product->source_site }}</li>
                    <li><span class="font-medium">Source item id:</span> {{ $product->source_item_id }}</li>
                </ul>

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

        <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Media ({{ $product->images->count() }})</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @forelse($product->images as $image)
                    <div class="border rounded-lg overflow-hidden bg-gray-50">
                        <img src="{{ storage_asset($image->image_path) }}" alt="Product image" class="w-full aspect-square object-cover">
                        <div class="p-2 text-xs text-gray-600 flex items-center justify-between">
                            <span>#{{ $image->position }}</span>
                            <form method="POST" action="{{ route('admin.products.images.destroy', ['product' => $product, 'image' => $image]) }}" onsubmit="return confirm('Remove this image?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">Remove</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 col-span-full">No media uploaded.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
