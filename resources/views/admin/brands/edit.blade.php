<x-admin-layout>

<div class="p-6 max-w-2xl mx-auto">

    <h2 class="text-2xl font-bold text-blue-700 mb-6">
        Edit Brand
    </h2>

    <div class="bg-white shadow rounded-lg p-6">

        <form method="POST"
              action="{{ route('admin.brands.update', $brand) }}"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block mb-2 font-medium">Category</label>
                <select name="category_id"
                        class="border p-2 rounded w-full"
                        required>

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ $brand->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Brand Name</label>
                <input type="text"
                       name="name"
                       value="{{ $brand->name }}"
                       class="border p-2 rounded w-full"
                       required>
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Brand Image</label>
                @php($brandImage = $brand->representative_image ?: $brand->image_path)
                @if($brandImage)
                    <img src="{{ asset('storage/' . $brandImage) }}"
                         alt="{{ $brand->name }}"
                         class="h-16 w-16 object-contain border rounded mb-2 p-1">
                @endif
                <input type="file"
                       name="image"
                       accept=".jpg,.jpeg,.png,.webp,image/*"
                       class="border p-2 rounded w-full">
            </div>

            <div class="mb-4">
                <label class="flex items-center space-x-2">
                    <input type="checkbox"
                           name="uses_pricing_engine"
                           value="1"
                           class="rounded"
                           {{ $brand->uses_pricing_engine ? 'checked' : '' }}>
                    <span>Enable Structured Pricing</span>
                </label>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.brands.index') }}"
                   class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Back
                </a>

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Update Brand
                </button>
            </div>

        </form>

    </div>

</div>

</x-admin-layout>
