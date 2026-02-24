<x-admin-layout>
    <div class="max-w-5xl mx-auto p-4 md:p-6">
        <h2 class="text-2xl font-bold text-blue-700 mb-4">{{ $brand->name }} Models (No Series)</h2>

        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded shadow p-4 mb-6">
            <form method="POST" action="{{ route('admin.brand-models.store', $brand) }}" class="flex flex-col md:flex-row gap-3" enctype="multipart/form-data">
                @csrf
                <input type="text" name="name" placeholder="Model name" class="flex-1 border rounded px-3 py-2" required>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/*" class="border rounded px-3 py-2">
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add Model</button>
            </form>
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
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
                                <form method="POST" action="{{ route('admin.models.toggle', $model) }}">
                                    @csrf
                                    <button class="px-3 py-1 rounded text-white {{ $model->is_active ? 'bg-green-600' : 'bg-gray-500' }}">
                                        {{ $model->is_active ? 'YES' : 'NO' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-3 text-center">
                                <form method="POST" action="{{ route('admin.models.destroy', $model) }}" onsubmit="return confirm('Delete this model?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">No brand-level models yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
