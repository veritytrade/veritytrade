<?php

use App\Models\FeatureFlag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('feature_flags')) {
            return;
        }

        FeatureFlag::updateOrCreate(
            ['key' => 'enable_logistics_update_emails'],
            [
                'value' => '1',
                'group' => 'mail',
                'description' => 'Email customers when shipment stage changes or new carrier tracking rows arrive (only while in transit; stops after dispatched)',
                'is_active' => true,
            ]
        );
        FeatureFlag::clearCache('enable_logistics_update_emails');
    }

    public function down(): void
    {
        if (! Schema::hasTable('feature_flags')) {
            return;
        }

        FeatureFlag::where('key', 'enable_logistics_update_emails')->delete();
        FeatureFlag::clearCache('enable_logistics_update_emails');
    }
};
