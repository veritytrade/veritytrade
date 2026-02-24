<x-admin-layout>
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-blue-700">User Management</h2>
            <p class="text-sm text-gray-500 mt-1">Approve users and assign roles with controlled access.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-3 text-left font-semibold">Name</th>
                        <th class="p-3 text-left font-semibold">Email</th>
                        <th class="p-3 text-center font-semibold">Approved</th>
                        <th class="p-3 text-center font-semibold">Role</th>
                        <th class="p-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        @php
                            $currentRoleId = $user->role_id ?? $user->roles->first()?->id;
                            $currentRoleName = $user->role->name ?? $user->roles->first()?->name ?? 'none';
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800">{{ $user->name }}</td>
                            <td class="p-3 text-gray-700">{{ $user->email }}</td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $user->is_approved ? 'APPROVED' : 'PENDING' }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ strtoupper($currentRoleName) }}
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    @if(!$user->is_approved)
                                        <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                            @csrf
                                            <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->user()->hasPermission('assign_roles'))
                                        <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            <select name="role_id" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ (int)$currentRoleId === (int)$role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                                                Set Role
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
