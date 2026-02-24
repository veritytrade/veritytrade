<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ShipmentBatch;
use App\Models\TrackingStage;
use Illuminate\Support\Facades\DB;

class ShipmentBatchService
{
    public function updateBatchStage(ShipmentBatch $batch, string $stage, ?int $updatedBy = null): ShipmentBatch
    {
        return DB::transaction(function () use ($batch, $stage, $updatedBy) {
            $batch->update(['current_stage' => $stage]);

            $stageNumber = $this->resolveStageNumber($stage);

            foreach ($batch->orders()->get() as $order) {
                $order->update(['current_stage' => $stage]);

                if ($stageNumber > 0) {
                    TrackingStage::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'stage_number' => $stageNumber,
                        ],
                        [
                            'stage_name' => $stage,
                            'completed_at' => now(),
                            'updated_by' => $updatedBy,
                        ]
                    );
                }
            }

            return $batch->fresh();
        });
    }

    public function reassignOrder(Order $order, ?ShipmentBatch $newBatch): Order
    {
        return DB::transaction(function () use ($order, $newBatch) {
            $order->assignToShipmentBatch($newBatch);

            return $order->fresh();
        });
    }

    private function resolveStageNumber(string $stageName): int
    {
        $map = [
            'Sent to logistics' => 1,
            'Arrived logistics' => 2,
            'Flying to Nigeria' => 3,
            'Arrived Lagos' => 4,
            'Sent to final destination' => 5,
        ];

        return $map[$stageName] ?? 0;
    }
}
