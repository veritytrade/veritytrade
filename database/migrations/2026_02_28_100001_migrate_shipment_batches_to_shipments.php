<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shipment_batches')) {
            return;
        }

        $stageIds = DB::table('tracking_stages')->pluck('id', 'position')->toArray();
        $firstStageId = $stageIds[1] ?? null;

        foreach (DB::table('shipment_batches')->get() as $batch) {
            DB::table('shipments')->insert([
                'chinese_tracking_code' => $batch->china_tracking_code ?? 'MIGRATED',
                'logistics_company' => 'Unknown',
                'current_stage_id' => $firstStageId,
                'status' => 'active',
                'created_at' => $batch->created_at ?? now(),
                'updated_at' => $batch->updated_at ?? now(),
            ]);
        }

        $batches = DB::table('shipment_batches')->orderBy('id')->get();
        $shipments = DB::table('shipments')->orderBy('id')->get();
        $batchToShipment = [];
        foreach ($batches as $i => $batch) {
            if (isset($shipments[$i])) {
                $batchToShipment[$batch->id] = $shipments[$i]->id;
            }
        }

        if (Schema::hasColumn('orders', 'shipment_batch_id')) {
            foreach ($batchToShipment as $batchId => $shipmentId) {
                DB::table('orders')->where('shipment_batch_id', $batchId)->update([
                    'shipment_id' => $shipmentId,
                ]);
            }
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shipment_batch_id');
            });
        }

        if (Schema::hasColumn('orders', 'current_stage')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('current_stage');
            });
        }

        Schema::dropIfExists('shipment_batches');
    }

    public function down(): void
    {
        // Reverse migration not implemented for simplicity
    }
};
