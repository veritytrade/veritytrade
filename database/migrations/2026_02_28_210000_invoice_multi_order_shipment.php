<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->change();
        });
        foreach (DB::table('invoice_requests')->whereNotNull('order_id')->get() as $ir) {
            $shipmentId = DB::table('orders')->where('id', $ir->order_id)->value('shipment_id');
            if ($shipmentId) {
                DB::table('invoice_requests')->where('id', $ir->id)->update(['shipment_id' => $shipmentId]);
            }
        }
        $all = DB::table('invoice_requests')->whereNotNull('shipment_id')->get();
        foreach ($all->groupBy('shipment_id') as $shipmentId => $groups) {
            foreach ($groups->skip(1) as $g) {
                DB::table('invoice_requests')->where('id', $g->id)->delete();
            }
        }
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->unique('shipment_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('shipment_id')->constrained()->nullOnDelete();
        });
        foreach (DB::table('invoices')->get() as $inv) {
            DB::table('orders')->where('id', $inv->order_id)->update(['invoice_id' => $inv->id]);
        }
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('uuid')->constrained()->nullOnDelete();
        });
        foreach (DB::table('invoices')->get() as $inv) {
            $userId = DB::table('orders')->where('invoice_id', $inv->id)->value('user_id');
            if ($userId) {
                DB::table('invoices')->where('id', $inv->id)->update(['user_id' => $userId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
        });
        foreach (DB::table('invoices')->get() as $inv) {
            $orderId = DB::table('orders')->where('invoice_id', $inv->id)->value('id');
            if ($orderId) {
                DB::table('invoices')->where('id', $inv->id)->update(['order_id' => $orderId]);
            }
        }
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
        Schema::table('invoice_requests', function (Blueprint $table) {
            $table->dropUnique(['shipment_id']);
            $table->dropForeign(['shipment_id']);
            $table->dropColumn('shipment_id');
            $table->foreignId('order_id')->nullable(false)->constrained()->cascadeOnDelete();
            $table->unique('order_id');
        });
    }
};
