<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'require_email_verification'
            $table->boolean('value')->default(false); // true/false toggle
            $table->string('label'); // Human-readable label for admin UI
            $table->text('description')->nullable(); // Optional help text
            $table->timestamps();
        });

        // Seed default settings
        DB::table('site_settings')->insert([
            [
                'key' => 'require_email_verification',
                'value' => false,
                'label' => 'Require email verification on signup',
                'description' => 'When OFF: Admin approves users manually. When ON: Users must verify email before login.'
            ],
            [
                'key' => 'maintenance_mode',
                'value' => false,
                'label' => 'Enable maintenance mode',
                'description' => 'When ON: Public site shows maintenance page. Admin panel remains accessible.'
            ],
            [
                'key' => 'enable_non_listed_requests',
                'value' => true,
                'label' => 'Allow non-listed device requests',
                'description' => 'When ON: Customers can request devices not in catalog.'
            ],
            [
                'key' => 'show_price_ranges',
                'value' => true,
                'label' => 'Show price ranges to customers',
                'description' => 'When OFF: Hide exact prices until request submitted.'
            ],
            [
                'key' => 'require_phone_on_request',
                'value' => true,
                'label' => 'Require phone number on device requests',
                'description' => 'When ON: Phone field mandatory for WhatsApp integration.'
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};