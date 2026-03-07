<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Console\Command;

class SyncOrderStatusCommand extends Command
{
    protected $signature = 'orders:sync-status {--dry-run : Show changes without applying}';

    protected $description = 'Recalculate order status from shipment stage (fixes orders with inconsistent status)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $orders = Order::with(['shipment.currentStage', 'currentStageOverride'])
            ->where(function ($q) {
                $q->whereNotNull('shipment_id')->orWhereNotNull('current_stage_id');
            })
            ->get();

        $fixed = 0;
        foreach ($orders as $order) {
            $correct = Order::deriveStatusFromStage(
                $order->shipment_id,
                $order->current_stage_id,
                $order->shipment
            );
            if ($order->status !== $correct) {
                $this->line("Order #{$order->id} ({$order->verity_tracking_code}): {$order->status} → {$correct}");
                if (!$dryRun) {
                    $order->update(['status' => $correct]);
                }
                $fixed++;
            }
        }

        if ($fixed === 0) {
            $this->info('All order statuses are correct.');
        } else {
            $this->info($dryRun ? "Would fix {$fixed} order(s). Run without --dry-run to apply." : "Fixed {$fixed} order(s).");
        }

        return 0;
    }
}
