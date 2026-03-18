<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-green-800">Phone Brands</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage phone brands, then add models</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2.5 rounded-lg font-medium whitespace-nowrap">← Dashboard</a>
                    <a href="{{ route('admin.phones.pricing-settings.index') }}" class="inline-flex items-center justify-center min-h-[44px] bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2.5 rounded-lg font-medium whitespace-nowrap">Pricing Settings</a>
                    <a href="{{ route('admin.phones.brands.create') }}" class="inline-flex items-center justify-center min-h-[44px] bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition whitespace-nowrap">
                        + Add Brand
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                 class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($brands as $brand)
                        <tr>
                            <td class="px-4 py-3">
                                @if($brand->image)
                                    <img src="{{ storage_asset($brand->image) }}" alt="" class="w-10 h-10 object-contain rounded-lg inline-block align-middle mr-2">
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $brand->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $brand->slug }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $brand->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm space-x-2">
                                <a href="{{ route('admin.phones.models.index', $brand) }}" class="text-green-600 hover:text-green-800 font-medium">Models</a>
                                <a href="{{ route('admin.phones.brands.edit', $brand) }}" class="text-green-600 hover:text-green-800">Edit</a>
                                <form action="{{ route('admin.phones.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('Delete this brand and all its models?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center">
                                <p class="text-gray-500 mb-4">No brands yet.</p>
                                <a href="{{ route('admin.phones.brands.create') }}" class="inline-flex items-center justify-center min-h-[44px] px-6 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold">+ Add your first brand</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-admin-layout>
