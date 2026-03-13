<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK first so we can drop the unique index it depends on
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->dropForeign(['shipment_id']);
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->dropUnique(['shipment_id']);
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->unique(['shipment_id', 'user_id']);
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->dropForeign(['shipment_id']);
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->dropUnique(['shipment_id', 'user_id']);
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->unique('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
        });
    }
};
