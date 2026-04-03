<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('tracking_stages')) {
            return;
        }

        DB::table('tracking_stages')
            ->where('position', 4)
            ->where('name', 'Flying to Nigeria')
            ->update([
                'name' => 'In Transit to Nigeria',
                'short_name' => 'En route',
                'description' => 'En route to Nigeria (air or sea)',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('tracking_stages')) {
            return;
        }

        DB::table('tracking_stages')
            ->where('position', 4)
            ->where('name', 'In Transit to Nigeria')
            ->update([
                'name' => 'Flying to Nigeria',
                'short_name' => 'In Transit',
                'description' => 'In transit to Nigeria',
                'updated_at' => now(),
            ]);
    }
};
