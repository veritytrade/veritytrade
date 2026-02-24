<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series', function (Blueprint $table) {
            // Only add column if it doesn't exist
            if (!Schema::hasColumn('series', 'image_path')) {
                $table->string('image_path')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            // Only drop column if it exists
            if (Schema::hasColumn('series', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};