<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('site_settings');
    }

    public function down(): void
    {
        // No rollback needed - original migration still exists if needed later
    }
};