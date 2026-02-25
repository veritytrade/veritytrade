<x-admin-layout>
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-blue-700">Staff Management</h2>
            <p class="text-sm text-gray-500 mt-1">Assign admin/staff roles only to already registered users by email.</p>
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

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <form method="POST" action="{{ route('admin.staff.assign-role') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-600 mb-1">Registered Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="user@example.com">
                    @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Role</label>
                    <select name="role_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select role</option>
                        @foreach($staffRoles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end">
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Assign Role</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-3 text-left font-semibold">Name</th>
                        <th class="p-3 text-left font-semibold">Email</th>
                        <th class="p-3 text-center font-semibold">Role</th>
                        <th class="p-3 text-center font-semibold">Approved</th>
                        <th class="p-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($staffUsers as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 text-gray-800 font-medium">{{ $user->name }}</td>
                            <td class="p-3 text-gray-700">{{ $user->email }}</td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ strtoupper($user->role?->name ?? 'N/A') }}</span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $user->is_approved ? 'YES' : 'NO' }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <form method="POST" action="{{ route('admin.staff.remove-role', $user) }}" onsubmit="return confirm('Remove this role and set user back to customer?')">
                                    @csrf
                                    <button class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                                        Remove Role
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">No admin/staff users assigned yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
