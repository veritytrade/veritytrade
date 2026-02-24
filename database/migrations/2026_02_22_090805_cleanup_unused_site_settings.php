<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Delete ALL existing settings (they're unused)
        DB::table('site_settings')->delete();
        
        // Seed ONLY what's actually used TODAY
        DB::table('site_settings')->insert([
            [
                'key' => 'enable_whatsapp_redirect',
                'value' => true,
                'label' => 'Enable WhatsApp Redirect',
                'description' => 'When OFF: Hide "Buy Now" buttons on hot deals'
            ],
            [
                'key' => 'whatsapp_business_number',
                'value' => false,
                'label' => 'WhatsApp Business Number',
                'description' => 'Format: 2348012345678 (without + or spaces)'
            ],
        ]);
    }

    public function down(): void
    {
        // No rollback needed - just re-run original seeder if needed
    }
};
