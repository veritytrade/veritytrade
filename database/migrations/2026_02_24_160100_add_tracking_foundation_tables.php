<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shipment_batches')) {
            Schema::create('shipment_batches', function (Blueprint $table) {
                $table->id();
                $table->string('china_tracking_code')->nullable()->index();
                $table->string('current_stage')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('orders', 'shipment_batch_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('shipment_batch_id')->nullable()->after('user_id')->constrained('shipment_batches')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('orders', 'verity_tracking_code')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('verity_tracking_code')->nullable()->unique()->after('tracking_code');
            });
        }

        if (!Schema::hasColumn('orders', 'current_stage')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('current_stage')->nullable()->after('status');
            });
        }

        if (!Schema::hasTable('tracking_stages')) {
            Schema::create('tracking_stages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->unsignedInteger('stage_number');
                $table->string('stage_name');
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['order_id', 'stage_number']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_stages');

        if (Schema::hasColumn('orders', 'current_stage')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('current_stage');
            });
        }

        if (Schema::hasColumn('orders', 'verity_tracking_code')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique(['verity_tracking_code']);
                $table->dropColumn('verity_tracking_code');
            });
        }

        if (Schema::hasColumn('orders', 'shipment_batch_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shipment_batch_id');
            });
        }

        Schema::dropIfExists('shipment_batches');
    }
};
