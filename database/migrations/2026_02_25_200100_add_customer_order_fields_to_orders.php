<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'full_description')) {
                $table->text('full_description')->nullable()->after('spec_summary');
            }
            if (!Schema::hasColumn('orders', 'outstanding_balance_ngn')) {
                $table->decimal('outstanding_balance_ngn', 14, 2)->default(0)->after('total_amount_ngn');
            }
            if (!Schema::hasColumn('orders', 'pays_logistics')) {
                $table->boolean('pays_logistics')->default(false)->after('payment_status');
            }
        });

        // Add pending_approval to status enum (MySQL)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'pending_approval', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'processing'");
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'pays_logistics')) {
                $table->dropColumn('pays_logistics');
            }
            if (Schema::hasColumn('orders', 'outstanding_balance_ngn')) {
                $table->dropColumn('outstanding_balance_ngn');
            }
            if (Schema::hasColumn('orders', 'full_description')) {
                $table->dropColumn('full_description');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'processing'");
        }
    }
};
