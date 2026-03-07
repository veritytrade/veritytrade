<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('phone_specs')) {
            return;
        }
        DB::table('phone_specs')->where('name', 'Condition')->update(['name' => 'Function']);
    }

    public function down(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('phone_specs')) {
            return;
        }
        DB::table('phone_specs')->where('name', 'Function')->update(['name' => 'Condition']);
    }
};
