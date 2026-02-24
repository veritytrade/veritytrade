<x-admin-layout>

<div class="p-6 max-w-2xl mx-auto">

    <h2 class="text-2xl font-bold text-blue-700 mb-6">
        Edit Series
    </h2>

    <div class="bg-white shadow rounded-lg p-6">

        <form method="POST"
              action="{{ route('admin.series.update', $series) }}"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block mb-2 font-medium">Brand</label>
                <select name="brand_id"
                        class="border p-2 rounded w-full"
                        required>

                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}"
                            {{ $series->brand_id == $brand->id ? 'selected' : '' }}>
                            {{ $brand->category->name }} -> {{ $brand->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Series Name</label>
                <input type="text"
                       name="name"
                       value="{{ $series->name }}"
                       class="border p-2 rounded w-full"
                       required>
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Series Image</label>
                @php($seriesImage = $series->representative_image ?: $series->image_path)
                @if($seriesImage)
                    <img src="{{ asset('storage/' . $seriesImage) }}"
                         alt="{{ $series->name }}"
                         class="h-16 w-16 object-contain border rounded mb-2 p-1">
                @endif
                <input type="file"
                       name="image"
                       accept=".jpg,.jpeg,.png,.webp,image/*"
                       class="border p-2 rounded w-full">
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.series.index') }}"
                   class="bg-gray-400 text-white px-4 py-2 rounded">
                    Back
                </a>

                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded">
                    Update
                </button>
            </div>

        </form>

    </div>

</div>

</x-admin-layout>
