<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'waybill_outstanding_ngn')) {
                $table->decimal('waybill_outstanding_ngn', 14, 2)->nullable()->after('outstanding_balance_ngn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'waybill_outstanding_ngn')) {
                $table->dropColumn('waybill_outstanding_ngn');
            }
        });
    }
};
