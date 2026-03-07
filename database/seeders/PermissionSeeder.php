<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_dashboard',
            'approve_orders',
            'create_order',
            'create_shipment',
            'assign_shipment',
            'update_shipment_stage',
            'override_order_stage',
            'view_tracking',
            'manage_tracking_stages',
            'manage_categories',
            'manage_brands',
            'manage_series',
            'manage_models',
            'manage_price_rules',
            'access_pricing_engine',
            'access_pricing_settings',
            'manage_deals',
            'view_requests',
            'manage_requests',
            'manage_orders',
            'generate_invoices',
            'access_tracking',
            'approve_users',
            'assign_roles',
            'manage_staff',
            'view_audit_logs',
            'manage_feature_flags',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission],
                ['description' => ucfirst(str_replace('_', ' ', $permission))]
            );
        }
    }
}
