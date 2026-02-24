<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')->insert([
            [
                'key' => 'enable_whatsapp_redirect',
                'value' => true,
                'label' => 'Enable WhatsApp redirect',
                'description' => 'When OFF: Hide "Buy Now" buttons site-wide'
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
        DB::table('site_settings')
            ->where('key', 'enable_whatsapp_redirect')
            ->orWhere('key', 'whatsapp_business_number')
            ->delete();
    }
};