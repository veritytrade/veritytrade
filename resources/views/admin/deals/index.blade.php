<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">
                        Hot Deals Management
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Manage your promotional deals</p>
                </div>
                
                <a href="{{ route('admin.deals.create') }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm whitespace-nowrap text-center">
                    + Add Hot Deal
                </a>
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

        {{-- Deals Table --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="p-3 md:p-4 text-left font-semibold">Title</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Price</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Expires</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Active</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($deals as $deal)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 md:p-4 font-medium text-gray-800">{{ $deal->title }}</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ $deal->price_display ?? '-' }}</td>
                            <td class="p-3 md:p-4">
                                @if($deal->expires_at)
                                    <span class="{{ $deal->expires_at->isPast() ? 'text-red-600' : 'text-green-600' }} font-medium">
                                        {{ $deal->expires_at->format('M d, H:i') }}
                                    </span>
                                @else
                                    <span class="text-gray-500">No expiry</span>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 text-center">
                                <form method="POST" action="{{ route('admin.deals.toggle', $deal) }}">
                                    @csrf
                                    <button type="submit"
                                        class="{{ $deal->is_active ? 'bg-green-600' : 'bg-gray-400' }} 
                                               text-white px-3 md:px-4 py-1.5 rounded-full text-xs font-medium transition hover:opacity-90">
                                        {{ $deal->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                    </button>
                                </form>
                            </td>
                            {{-- ✅ FIXED DELETE BUTTON --}}
                            <td class="p-3 md:p-4 text-center">
                                <form method="POST" 
                                      action="{{ route('admin.deals.destroy', $deal) }}"
                                      onsubmit="return confirm('Delete this deal? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE') {{-- ✅ CRITICAL: Must match route --}}
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
                                No hot deals created yet. Create your first deal above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>