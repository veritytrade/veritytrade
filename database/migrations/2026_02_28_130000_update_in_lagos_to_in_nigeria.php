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
            ->update([
                'name' => 'Arrived Nigeria',
                'short_name' => 'In Nigeria',
                'description' => 'Landed in Nigeria',
            ]);
    }

    public function down(): void
    {
        DB::table('tracking_stages')
            ->where('position', 4)
            ->update([
                'name' => 'Arrived Lagos',
                'short_name' => 'In Lagos',
                'description' => 'Landed in Lagos',
            ]);
    }
};
