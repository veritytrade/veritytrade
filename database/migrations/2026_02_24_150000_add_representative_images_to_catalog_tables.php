<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'representative_image')) {
                $table->string('representative_image')->nullable()->after('name');
            }
        });

        Schema::table('series', function (Blueprint $table) {
            if (!Schema::hasColumn('series', 'representative_image')) {
                $table->string('representative_image')->nullable()->after('name');
            }
        });

        Schema::table('models', function (Blueprint $table) {
            if (!Schema::hasColumn('models', 'image_path')) {
                $table->string('image_path')->nullable()->after('name');
            }

            if (!Schema::hasColumn('models', 'representative_image')) {
                $table->string('representative_image')->nullable()->after('image_path');
            }
        });

        DB::table('brands')->whereNull('representative_image')->update([
            'representative_image' => DB::raw('image_path')
        ]);

        DB::table('series')->whereNull('representative_image')->update([
            'representative_image' => DB::raw('image_path')
        ]);

        if (Schema::hasColumn('models', 'image_path')) {
            DB::table('models')->whereNull('representative_image')->update([
                'representative_image' => DB::raw('image_path')
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (Schema::hasColumn('brands', 'representative_image')) {
                $table->dropColumn('representative_image');
            }
        });

        Schema::table('series', function (Blueprint $table) {
            if (Schema::hasColumn('series', 'representative_image')) {
                $table->dropColumn('representative_image');
            }
        });

        Schema::table('models', function (Blueprint $table) {
            if (Schema::hasColumn('models', 'representative_image')) {
                $table->dropColumn('representative_image');
            }

            if (Schema::hasColumn('models', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
