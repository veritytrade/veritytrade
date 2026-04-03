<?php

namespace Database\Seeders;

use App\Models\TrackingStage;
use Illuminate\Database\Seeder;

class TrackingStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Processing', 'short_name' => 'Processing', 'position' => 1, 'description' => 'Order approved; preparing for logistics handoff', 'color_code' => '#6b7280'],
            ['name' => 'Sent to Logistics', 'short_name' => 'Sent', 'position' => 2, 'description' => 'Package handed to logistics provider', 'color_code' => '#6b7280'],
            ['name' => 'Arrived Logistics', 'short_name' => 'At Logistics', 'position' => 3, 'description' => 'Received at logistics warehouse', 'color_code' => '#6b7280'],
            ['name' => 'In Transit to Nigeria', 'short_name' => 'En route', 'position' => 4, 'description' => 'En route to Nigeria (air or sea)', 'color_code' => '#6b7280'],
            ['name' => 'Arrived Nigeria', 'short_name' => 'In Nigeria', 'position' => 5, 'description' => 'Landed in Nigeria', 'color_code' => '#6b7280'],
            ['name' => 'Sent to Final Destination', 'short_name' => 'Dispatched', 'position' => 6, 'description' => 'Dispatched for delivery', 'color_code' => '#6b7280'],
            ['name' => 'Delivered', 'short_name' => 'Delivered', 'position' => 7, 'description' => 'Successfully delivered', 'color_code' => '#22c55e'],
        ];

        foreach ($stages as $stage) {
            TrackingStage::updateOrCreate(
                ['position' => $stage['position']],
                $stage
            );
        }
    }
}
