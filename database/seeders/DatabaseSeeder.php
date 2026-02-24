<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/*
|--------------------------------------------------------------------------
| Main Database Seeder
|--------------------------------------------------------------------------
| This runs all other seeders.
|--------------------------------------------------------------------------
*/

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            FeatureFlagSeeder::class,
            SuperAdminSeeder::class,
            PricingEngineSeeder::class,
        ]);
    }
}

