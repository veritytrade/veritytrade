<x-admin-layout>
<div class="p-6">

    <h2 class="text-2xl font-bold text-blue-700 mb-4">
        Manage Models — {{ $series->name }}
    </h2>

    @if(session('success'))
        <div x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity">
            {{ session('error') }}
        </div>
    @endif

    <!-- Create -->
    <div class="bg-white p-6 mb-6 shadow rounded-lg max-w-lg">
        <form method="POST"
              action="{{ route('admin.models.store', $series->id) }}"
              enctype="multipart/form-data">
            @csrf

            <div class="flex flex-col md:flex-row gap-3">
                <input type="text"
                       name="name"
                       placeholder="Model Name (e.g. 17 Pro Max)"
                       class="border p-2 rounded w-full"
                       required>
                <input type="file"
                       name="image"
                       accept=".jpg,.jpeg,.png,.webp,image/*"
                       class="border p-2 rounded w-full md:w-auto">

                <button class="bg-blue-600 text-white px-4 py-2 rounded">
                    Save
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="p-3 text-left">Image</th>
                    <th class="p-3 text-left">Model</th>
                    <th class="p-3 text-center">Active</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($models as $model)
                <tr class="border-t">
                    <td class="p-3">
                        @php($modelImage = $model->representative_image ?: $model->image_path)
                        @if($modelImage)
                            <img src="{{ asset('storage/' . $modelImage) }}" alt="{{ $model->name }}" class="h-12 w-12 rounded object-cover border">
                        @else
                            <div class="h-12 w-12 rounded bg-gray-100 border"></div>
                        @endif
                    </td>
                    <td class="p-3">{{ $model->name }}</td>

                    <td class="p-3 text-center">
                        <form method="POST"
                              action="{{ route('admin.models.toggle', $model) }}">
                            @csrf
                            <button class="{{ $model->is_active ? 'bg-green-600' : 'bg-gray-500' }}
                                           text-white px-3 py-1 rounded">
                                {{ $model->is_active ? 'YES' : 'NO' }}
                            </button>
                        </form>
                    </td>

                    <td class="p-3 text-center">
                        <form method="POST"
                              action="{{ route('admin.models.destroy', $model) }}">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-600 text-white px-3 py-1 rounded">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        No models created yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-admin-layout>
