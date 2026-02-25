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

        DB::table('feature_flags')->updateOrInsert(
            ['key' => 'enable_customer_address'],
            [
                'value' => '0',
                'group' => 'profile',
                'description' => 'Enable customer address field in profile',
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

        DB::table('feature_flags')->where('key', 'enable_customer_address')->delete();
    }
};

