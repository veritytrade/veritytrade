<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }

            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('approved_by')->constrained('roles')->nullOnDelete();
            }
        });

        Schema::table('models', function (Blueprint $table) {
            if (!Schema::hasColumn('models', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }

            if (!Schema::hasColumn('models', 'brand_id')) {
                $table->foreignId('brand_id')->nullable()->after('uuid')->constrained('brands')->nullOnDelete();
                $table->index(['brand_id', 'series_id'], 'models_brand_series_idx');
            }
        });

        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }

            if (!Schema::hasColumn('deals', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('position')->constrained('users')->nullOnDelete();
            }
        });

        // Backfill brand_id from series -> brand relation for existing models
        if (Schema::hasColumn('models', 'brand_id')) {
            $rows = DB::table('models as m')
                ->join('series as s', 's.id', '=', 'm.series_id')
                ->select('m.id as model_id', 's.brand_id')
                ->get();

            foreach ($rows as $row) {
                DB::table('models')->where('id', $row->model_id)->update(['brand_id' => $row->brand_id]);
            }
        }

        // Make series_id nullable for brands without series pipeline
        if (Schema::hasColumn('models', 'series_id') && DB::getDriverName() !== 'sqlite') {
            Schema::table('models', function (Blueprint $table) {
                $table->foreignId('series_id')->nullable()->change();
            });
        }

        // Generate uuids for existing rows
        foreach (DB::table('users')->whereNull('uuid')->select('id')->cursor() as $row) {
            DB::table('users')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
        }

        foreach (DB::table('models')->whereNull('uuid')->select('id')->cursor() as $row) {
            DB::table('models')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
        }

        foreach (DB::table('deals')->whereNull('uuid')->select('id')->cursor() as $row) {
            DB::table('deals')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
        }

        // Backfill users.role_id from role_user pivot when available
        if (Schema::hasTable('role_user') && Schema::hasColumn('users', 'role_id')) {
            $pivotRows = DB::table('role_user')->select('user_id', 'role_id')->get();
            foreach ($pivotRows as $pivot) {
                DB::table('users')->where('id', $pivot->user_id)->whereNull('role_id')->update(['role_id' => $pivot->role_id]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('uuid', 'users_uuid_unique_idx');
        });

        Schema::table('models', function (Blueprint $table) {
            $table->unique('uuid', 'models_uuid_unique_idx');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->unique('uuid', 'deals_uuid_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropConstrainedForeignId('role_id');
            }

            if (Schema::hasColumn('users', 'uuid')) {
                $table->dropUnique('users_uuid_unique_idx');
                $table->dropColumn('uuid');
            }
        });

        Schema::table('models', function (Blueprint $table) {
            if (Schema::hasColumn('models', 'brand_id')) {
                $table->dropIndex('models_brand_series_idx');
                $table->dropConstrainedForeignId('brand_id');
            }

            if (Schema::hasColumn('models', 'uuid')) {
                $table->dropUnique('models_uuid_unique_idx');
                $table->dropColumn('uuid');
            }

            if (Schema::hasColumn('models', 'series_id') && DB::getDriverName() !== 'sqlite') {
                $table->foreignId('series_id')->nullable(false)->change();
            }
        });

        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('deals', 'uuid')) {
                $table->dropUnique('deals_uuid_unique_idx');
                $table->dropColumn('uuid');
            }
        });
    }
};
