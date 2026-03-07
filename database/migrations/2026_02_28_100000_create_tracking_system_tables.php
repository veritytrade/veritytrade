<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop old order-scoped tracking_stages and create config table
        Schema::dropIfExists('tracking_stages');
        Schema::create('tracking_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('position');
            $table->string('description')->nullable();
            $table->string('color_code')->default('#6b7280');
            $table->timestamps();
        });

        // 2. Create shipments (replaces shipment_batches concept)
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('chinese_tracking_code');
            $table->string('logistics_company');
            $table->foreignId('current_stage_id')->nullable()->constrained('tracking_stages')->nullOnDelete();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Alter orders for new tracking system
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'product_name')) {
                    $table->string('product_name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('orders', 'spec_summary')) {
                    $table->string('spec_summary')->nullable()->after('product_name');
                }
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status')->default('pending')->after('total_amount_ngn');
                }
                if (!Schema::hasColumn('orders', 'shipment_id')) {
                    $table->foreignId('shipment_id')->nullable()->after('status')->constrained('shipments')->nullOnDelete();
                }
                if (!Schema::hasColumn('orders', 'current_stage_id')) {
                    $table->foreignId('current_stage_id')->nullable()->after('shipment_id')->constrained('tracking_stages')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'current_stage_id')) {
                    $table->dropConstrainedForeignId('current_stage_id');
                }
                if (Schema::hasColumn('orders', 'shipment_id')) {
                    $table->dropConstrainedForeignId('shipment_id');
                }
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $table->dropColumn('payment_status');
                }
                if (Schema::hasColumn('orders', 'spec_summary')) {
                    $table->dropColumn('spec_summary');
                }
                if (Schema::hasColumn('orders', 'product_name')) {
                    $table->dropColumn('product_name');
                }
            });
        }
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('tracking_stages');
    }
};
