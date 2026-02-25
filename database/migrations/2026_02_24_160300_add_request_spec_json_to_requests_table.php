<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('requests', 'request_spec_json')) {
            Schema::table('requests', function (Blueprint $table) {
                $table->json('request_spec_json')->nullable()->after('appearance_grade_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('requests', 'request_spec_json')) {
            Schema::table('requests', function (Blueprint $table) {
                $table->dropColumn('request_spec_json');
            });
        }
    }
};
