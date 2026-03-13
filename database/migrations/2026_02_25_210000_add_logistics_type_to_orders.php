<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $afterCol = Schema::hasColumn('orders', 'payment_status') ? 'payment_status' : 'total_amount_ngn';
        Schema::table('orders', function (Blueprint $table) use ($afterCol) {
            if (!Schema::hasColumn('orders', 'logistics_type')) {
                $table->string('logistics_type', 32)->default('within_lagos')->after($afterCol);
            }
        });

        if (Schema::hasColumn('orders', 'pays_logistics')) {
            DB::table('orders')->where('pays_logistics', true)->update(['logistics_type' => 'outside_lagos']);
            DB::table('orders')->where('pays_logistics', false)->update(['logistics_type' => 'within_lagos']);
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('pays_logistics');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('orders', 'pays_logistics')) {
            $paysAfter = Schema::hasColumn('orders', 'payment_status') ? 'payment_status' : 'total_amount_ngn';
            Schema::table('orders', function (Blueprint $table) use ($paysAfter) {
                $table->boolean('pays_logistics')->default(false)->after($paysAfter);
            });
            DB::table('orders')->whereIn('logistics_type', ['outside_lagos'])->update(['pays_logistics' => true]);
            DB::table('orders')->whereIn('logistics_type', ['within_lagos', 'combined'])->update(['pays_logistics' => false]);
        }
        if (Schema::hasColumn('orders', 'logistics_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('logistics_type');
            });
        }
    }
};
