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
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200 p-4 md:p-6">
                <h3 class="font-semibold text-gray-900 mb-2">Customer-facing description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $product->description_en ?: 'No description yet.' }}</p>

                <h3 class="font-semibold text-gray-900 mt-6 mb-2">Specs JSON</h3>
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded p-3 overflow-x-auto">{{ json_encode($product->specs_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: 'null' }}</pre>

                <h3 class="font-semibold text-gray-900 mt-6 mb-2">Condition notes</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $product->condition_notes ?: 'No condition notes.' }}</p>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
                <h3 class="font-semibold text-gray-900 mb-3">Private source details</h3>
                <ul class="text-sm space-y-2 text-gray-700">
                    <li><span class="font-medium">Status:</span> {{ $product->status }}</li>
                    <li><span class="font-medium">Price:</span> ₦{{ number_format((int) $product->price_ngn) }}</li>
                    <li><span class="font-medium">Stock:</span> {{ (int) $product->stock }}</li>
                    <li><span class="font-medium">Source site:</span> {{ $product->source_site }}</li>
                    <li><span class="font-medium">Source item id:</span> {{ $product->source_item_id }}</li>
                    <li class="break-all"><span class="font-medium">Source URL:</span> {{ $product->source_url_private ?: 'N/A' }}</li>
                </ul>
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
