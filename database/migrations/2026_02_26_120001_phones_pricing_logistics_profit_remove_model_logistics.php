<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('phone_pricing_settings')) {
            Schema::table('phone_pricing_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('phone_pricing_settings', 'logistics_cny')) {
                    $table->decimal('logistics_cny', 12, 2)->default(0)->after('exchange_rate');
                }
                if (!Schema::hasColumn('phone_pricing_settings', 'profit_margin_ngn')) {
                    $table->decimal('profit_margin_ngn', 12, 2)->default(0)->after('rounding_unit');
                }
            });
        }

        if (Schema::hasTable('phone_models') && Schema::hasColumn('phone_models', 'logistics_cost_cny')) {
            Schema::table('phone_models', function (Blueprint $table) {
                $table->dropColumn('logistics_cost_cny');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('phone_pricing_settings')) {
            Schema::table('phone_pricing_settings', function (Blueprint $table) {
                if (Schema::hasColumn('phone_pricing_settings', 'logistics_cny')) {
                    $table->dropColumn('logistics_cny');
                }
                if (Schema::hasColumn('phone_pricing_settings', 'profit_margin_ngn')) {
                    $table->dropColumn('profit_margin_ngn');
                }
            });
        }

        if (Schema::hasTable('phone_models')) {
            Schema::table('phone_models', function (Blueprint $table) {
                $table->decimal('logistics_cost_cny', 12, 2)->default(0)->after('image');
            });
        }
    }
};
