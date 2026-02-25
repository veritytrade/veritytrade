<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('feature_flags')) {
            return;
        }

        $legacyWhatsapp = DB::table('feature_flags')->where('key', 'whatsapp_business_number')->value('value');

        DB::table('feature_flags')->whereIn('key', [
            'enable_hot_deals',
            'enable_phone_pricing',
            'whatsapp_business_number',
        ])->delete();

        DB::table('feature_flags')->update(['is_active' => true]);

        DB::table('feature_flags')->updateOrInsert(
            ['key' => 'require_email_verification'],
            [
                'value' => '1',
                'group' => 'auth',
                'description' => 'Require 6-digit email verification code before login',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('feature_flags')->updateOrInsert(
            ['key' => 'require_admin_approval'],
            [
                'value' => '1',
                'group' => 'auth',
                'description' => 'Require admin approval before login',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('feature_flags')->updateOrInsert(
            ['key' => 'whatsapp_number'],
            [
                'value' => $legacyWhatsapp ?: '2347084117779',
                'group' => 'public',
                'description' => 'Primary WhatsApp business number',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('feature_flags')) {
            return;
        }

        DB::table('feature_flags')->where('key', 'require_admin_approval')->delete();
    }
};

