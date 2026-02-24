<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if (!$superAdminRole) {
            return;
        }

        $email = env('SUPER_ADMIN_EMAIL', 'admin@veritytrade.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'password123');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'password' => Hash::make($password),
                'phone' => env('SUPER_ADMIN_PHONE', '0000000000'),
                'address' => env('SUPER_ADMIN_ADDRESS', 'Head Office'),
                'is_approved' => true,
                'approved_at' => now(),
                'email_verified_at' => now(),
                'role_id' => $superAdminRole->id,
            ]
        );

        $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
    }
}
