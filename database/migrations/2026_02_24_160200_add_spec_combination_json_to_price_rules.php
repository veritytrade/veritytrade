<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('price_rules', 'spec_combination_json')) {
            Schema::table('price_rules', function (Blueprint $table) {
                $table->json('spec_combination_json')->nullable()->after('appearance_grade_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('price_rules', 'spec_combination_json')) {
            Schema::table('price_rules', function (Blueprint $table) {
                $table->dropColumn('spec_combination_json');
            });
        }
    }
};
