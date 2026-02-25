<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')
            ->whereNotIn('name', ['customer', 'super_admin'])
            ->orderBy('name')
            ->get();
        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->name, ['customer', 'super_admin'], true)) {
            abort(403, 'This role permissions are not managed from this panel.');
        }

        $permissionIds = collect($request->input('permissions', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        $role->permissions()->sync($permissionIds);

        return back()->with('success', 'Role permissions updated successfully.');
    }
}
