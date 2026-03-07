<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl font-bold text-green-800 mb-4">Edit Brand: {{ $brand->name }}</h2>
            <form action="{{ route('admin.phones.brands.update', $brand) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" required
                           class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:border-green-500 focus:ring-green-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image (optional)</label>
                    @if($brand->image)
                        <p class="text-xs text-gray-500 mb-1">Current: <img src="{{ asset('storage/'.$brand->image) }}" alt="" class="w-8 h-8 object-contain inline-block rounded"></p>
                    @endif
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700">
                    @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium">Update</button>
                    <a href="{{ route('admin.phones.brands.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
