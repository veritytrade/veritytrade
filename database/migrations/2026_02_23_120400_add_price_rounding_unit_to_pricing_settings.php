<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pricing_settings', 'price_rounding_unit')) {
                $table->unsignedInteger('price_rounding_unit')->default(10000)->after('fixed_margin_ngn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pricing_settings', function (Blueprint $table) {
            if (Schema::hasColumn('pricing_settings', 'price_rounding_unit')) {
                $table->dropColumn('price_rounding_unit');
            }
        });
    }
};
