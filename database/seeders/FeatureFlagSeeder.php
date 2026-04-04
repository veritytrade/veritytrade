<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FeatureFlag::adminVisibleDefaults() as $key => $payload) {
            FeatureFlag::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $payload['value'],
                    'group' => $payload['group'],
                    'description' => $payload['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
