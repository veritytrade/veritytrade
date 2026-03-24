<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('orders', 'supplier_platform')) {
                $table->string('supplier_platform', 50)->nullable()->after('tracking_code');
            }
            if (!Schema::hasColumn('orders', 'supplier_order_number')) {
                $table->string('supplier_order_number', 120)->nullable()->after('supplier_platform');
                $table->unique('supplier_order_number');
            }
            if (!Schema::hasColumn('orders', 'supplier_logistics_code')) {
                $table->string('supplier_logistics_code', 120)->nullable()->after('supplier_order_number');
                $table->unique('supplier_logistics_code');
            }
            if (!Schema::hasColumn('orders', 'mapping_status')) {
                $table->string('mapping_status', 30)->nullable()->after('supplier_logistics_code');
            }
            if (!Schema::hasColumn('orders', 'mapped_at')) {
                $table->timestamp('mapped_at')->nullable()->after('mapping_status');
            }
            if (!Schema::hasColumn('orders', 'mapped_by')) {
                $table->foreignId('mapped_by')->nullable()->after('mapped_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'mapped_by')) {
                $table->dropConstrainedForeignId('mapped_by');
            }
            if (Schema::hasColumn('orders', 'mapped_at')) {
                $table->dropColumn('mapped_at');
            }
            if (Schema::hasColumn('orders', 'mapping_status')) {
                $table->dropColumn('mapping_status');
            }
            if (Schema::hasColumn('orders', 'supplier_logistics_code')) {
                $table->dropUnique('orders_supplier_logistics_code_unique');
                $table->dropColumn('supplier_logistics_code');
            }
            if (Schema::hasColumn('orders', 'supplier_order_number')) {
                $table->dropUnique('orders_supplier_order_number_unique');
                $table->dropColumn('supplier_order_number');
            }
            if (Schema::hasColumn('orders', 'supplier_platform')) {
                $table->dropColumn('supplier_platform');
            }
        });
    }
};

