<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('price_display')->nullable(); // Marketing text: "₦450,000" or "450k"
            $table->text('whatsapp_message')->nullable(); // Optional custom WhatsApp message
            $table->dateTime('expires_at'); // Required expiry (max 3 days from creation)
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_active', 'expires_at', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};