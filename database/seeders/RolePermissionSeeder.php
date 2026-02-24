<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $staff = Role::where('name', 'staff')->first();
        $customer = Role::where('name', 'customer')->first();

        $allPermissionIds = Permission::query()->pluck('id')->all();

        if ($superAdmin) {
            $superAdmin->permissions()->sync($allPermissionIds);
        }

        if ($admin) {
            $adminPermissionIds = Permission::query()
                ->whereNotIn('name', ['assign_roles', 'manage_staff', 'manage_feature_flags'])
                ->pluck('id')
                ->all();
            $admin->permissions()->sync($adminPermissionIds);
        }

        if ($staff) {
            $staffPermissionIds = Permission::query()->whereIn('name', [
                'view_dashboard',
                'manage_deals',
                'view_requests',
                'manage_requests',
                'manage_orders',
                'generate_invoices',
                'access_tracking',
            ])->pluck('id')->all();

            $staff->permissions()->sync($staffPermissionIds);
        }

        if ($customer) {
            $customer->permissions()->sync([]);
        }
    }
}
