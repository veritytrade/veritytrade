<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        
        {{-- Header (Horizontal Layout - Mobile Adapted) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Left: Title --}}
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">
                        Category Management
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Organize your product categories</p>
                </div>
                
                {{-- Right: Create Form (Horizontal) --}}
                <form method="POST" action="{{ route('admin.categories.store') }}" class="flex-1">
                    @csrf
                    <div class="flex gap-3">
                        <input type="text" name="name" 
                               placeholder="Enter category name..."
                               class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               required>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm whitespace-nowrap">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Flash Messages --}}
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

        {{-- Categories Table (Mobile Adapted) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="p-3 md:p-4 text-left font-semibold">Name</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Active</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 md:p-4 font-medium text-gray-800">{{ $category->name }}</td>
                            <td class="p-3 md:p-4 text-center">
                                <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                                    @csrf
                                    <button type="submit"
                                        class="{{ $category->is_active ? 'bg-green-600' : 'bg-gray-400' }} 
                                               text-white px-3 md:px-4 py-1.5 rounded-full text-xs font-medium transition hover:opacity-90">
                                        {{ $category->is_active ? 'YES' : 'NO' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-3 md:p-4 text-center space-x-2">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-1.5 rounded-lg text-xs font-medium transition">
                                    Edit
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.categories.destroy', $category) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 md:px-4 py-1.5 rounded-lg text-xs font-medium transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-6 md:p-8 text-center text-gray-500">
                                No categories created yet. Create your first category above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>