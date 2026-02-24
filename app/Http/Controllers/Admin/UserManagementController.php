<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $actor = auth()->user();
        $actorIsSuperAdmin = $actor && $actor->hasRole('super_admin');

        $usersQuery = User::with(['role', 'roles'])->orderByDesc('id');
        $rolesQuery = Role::orderBy('name');

        // Non-super-admin users must not see/manage super_admin accounts.
        if (!$actorIsSuperAdmin) {
            $superAdminRoleId = Role::where('name', 'super_admin')->value('id');
            if ($superAdminRoleId) {
                $usersQuery->where('role_id', '!=', $superAdminRoleId);
                $rolesQuery->where('name', '!=', 'super_admin');
            }
        }

        $users = $usersQuery->get();
        $roles = $rolesQuery->get();

        return view('admin.users.index', compact('users', 'roles', 'actorIsSuperAdmin'));
    }

    public function approve(User $user): RedirectResponse
    {
        $before = $user->toArray();
        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        Audit::log('approve_user', 'users', $user->id, $before, $user->fresh()->toArray());

        return back()->with('success', 'User approved successfully.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->integer('role_id'));
        $actor = auth()->user();
        $actorIsSuperAdmin = $actor && $actor->hasRole('super_admin');
        $targetIsSuperAdmin = $user->hasRole('super_admin');

        if (!$actorIsSuperAdmin && ($role->name === 'super_admin' || $targetIsSuperAdmin)) {
            abort(403, 'Only super admin can assign or manage super admin roles.');
        }

        $before = $user->toArray();
        $user->assignRole($role);
        Audit::log('assign_role', 'users', $user->id, $before, $user->fresh()->toArray());

        return back()->with('success', 'Role updated successfully.');
    }
}
