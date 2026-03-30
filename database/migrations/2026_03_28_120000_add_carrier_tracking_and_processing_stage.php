<?php

use App\Models\TrackingStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->longText('carrier_tracks_json')->nullable()->after('logistics_company');
            $table->timestamp('carrier_tracks_synced_at')->nullable()->after('carrier_tracks_json');
        });

        if (! Schema::hasTable('tracking_stages')) {
            return;
        }

        // Only when legacy six stages exist (no Processing yet). Fresh installs get seven stages from TrackingStageSeeder.
        if (TrackingStage::where('name', 'Processing')->exists()) {
            return;
        }

        if (TrackingStage::count() !== 6) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function (): void {
            foreach (TrackingStage::orderByDesc('position')->get() as $stage) {
                $stage->update(['position' => $stage->position + 1]);
            }

            TrackingStage::create([
                'name' => 'Processing',
                'short_name' => 'Processing',
                'position' => 1,
                'description' => 'Order approved; preparing for logistics handoff',
                'color_code' => '#6b7280',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['carrier_tracks_json', 'carrier_tracks_synced_at']);
        });

        if (! Schema::hasTable('tracking_stages')) {
            return;
        }

        $processing = TrackingStage::where('name', 'Processing')->first();
        if (! $processing) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($processing): void {
            $processing->delete();
            foreach (TrackingStage::orderBy('position')->get() as $stage) {
                if ((int) $stage->position > 1) {
                    $stage->update(['position' => $stage->position - 1]);
                }
            }
        });
    }
};
