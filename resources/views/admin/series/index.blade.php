<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        
        {{-- Header (Horizontal Layout) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Left: Title --}}
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">
                        Series Management
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Manage product series</p>
                </div>
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

        {{-- Filter Section (Horizontal) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h3 class="font-semibold text-gray-700 mb-4">Filter Series</h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <select name="category"
                        onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="brand"
                        onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <option value="">Select Brand</option>
                    @foreach($brands as $brand)
                        @if(!request('category') || $brand->category_id == request('category'))
                            <option value="{{ $brand->id }}"
                                {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Create Series (Horizontal) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h3 class="font-semibold text-gray-700 mb-4">Create New Series</h3>
            
            @if(request('brand'))
                <form method="POST" action="{{ route('admin.series.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="brand_id" value="{{ request('brand') }}">

                    <div class="mb-4 text-sm text-gray-600">
                        @php($selectedBrand = $brands->firstWhere('id', request('brand')))
                        Creating under:
                        <strong class="text-blue-700">
                            {{ optional(optional($selectedBrand)->category)->name ?? 'Unknown Category' }}
                            ->
                            {{ optional($selectedBrand)->name ?? 'Unknown Brand' }}
                        </strong>
                    </div>

                    <div class="flex flex-col md:flex-row gap-3">
                        <input type="text"
                               name="name"
                               placeholder="Series Name (e.g. iPhone 15 Series)"
                               class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               required>
                        <input type="file"
                               name="image"
                               accept=".jpg,.jpeg,.png,.webp,image/*"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-600">

                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm whitespace-nowrap">
                            Add Series
                        </button>
                    </div>
                </form>
            @else
                <div class="text-gray-500 text-sm bg-gray-50 p-4 rounded-lg">
                    📌 Select a Category and Brand above to create a new series.
                </div>
            @endif
        </div>

        {{-- Series Table --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="p-3 md:p-4 text-left font-semibold">Series</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Brand</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Category</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Active</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($series as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 md:p-4 font-medium text-gray-800">{{ $item->name }}</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ optional($item->brand)->name ?? 'Unknown Brand' }}</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ optional(optional($item->brand)->category)->name ?? 'Unknown Category' }}</td>
                            <td class="p-3 md:p-4 text-center">
                                <form method="POST" action="{{ route('admin.series.toggle', $item) }}">
                                    @csrf
                                    <button type="submit"
                                        class="{{ $item->is_active ? 'bg-green-600' : 'bg-gray-400' }} 
                                               text-white px-3 md:px-4 py-1.5 rounded-full text-xs font-medium transition hover:opacity-90">
                                        {{ $item->is_active ? 'YES' : 'NO' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-3 md:p-4 text-center space-x-2">
                                <a href="{{ route('admin.models.index', $item) }}"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 md:px-4 py-1.5 rounded-lg text-xs font-medium transition">
                                    Models
                                </a>
                                <a href="{{ route('admin.series.edit', $item) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-1.5 rounded-lg text-xs font-medium transition">
                                    Edit
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.series.destroy', $item) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete this series?')">
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
                            <td colspan="5" class="p-6 md:p-8 text-center text-gray-500">
                                No series found. Create your first series above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>

