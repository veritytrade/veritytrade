<x-admin-layout>
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-blue-700">Registered Users</h2>
            <p class="text-sm text-gray-500 mt-1">View all registered users, approve accounts, and delete accounts when needed.</p>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 2500)"
                 x-show="show"
                 x-transition
                 class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 3000)"
                 x-show="show"
                 x-transition
                 class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @php($showAddress = feature_enabled('enable_customer_address', false))

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-3 text-left font-semibold">Name</th>
                        <th class="p-3 text-left font-semibold">Email</th>
                        <th class="p-3 text-left font-semibold">Phone Number</th>
                        <th class="p-3 text-left font-semibold">City</th>
                        <th class="p-3 text-left font-semibold">State</th>
                        @if($showAddress)
                            <th class="p-3 text-left font-semibold">Address</th>
                        @endif
                        <th class="p-3 text-center font-semibold">Role</th>
                        <th class="p-3 text-center font-semibold">Approved</th>
                        <th class="p-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 text-gray-800 font-medium">{{ $user->name }}</td>
                            <td class="p-3 text-gray-700">{{ $user->email }}</td>
                            @php($cleanPhone = preg_match('/^\+?[0-9]{6,20}$/', (string) $user->phone) ? (string) $user->phone : '-')
                            <td class="p-3 text-gray-700">{{ $cleanPhone }}</td>
                            <td class="p-3 text-gray-700">{{ $user->city ?: '-' }}</td>
                            <td class="p-3 text-gray-700">{{ $user->state ?: '-' }}</td>
                            @if($showAddress)
                                <td class="p-3 text-gray-700">{{ $user->address ?: '-' }}</td>
                            @endif
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ strtoupper($user->role?->name ?? 'NONE') }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $user->is_approved ? 'YES' : 'NO' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    @if(!$user->is_approved)
                                        <form method="POST" action="{{ route('admin.registered-users.approve', $user) }}">
                                            @csrf
                                            <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.registered-users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showAddress ? 9 : 8 }}" class="p-6 text-center text-gray-500">No registered users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
