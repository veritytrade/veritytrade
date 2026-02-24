<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->string('image_path'); // Path relative to storage/app/public/deals/
            $table->integer('position')->default(0);
            $table->timestamps();

            // Index for performance
            $table->index('deal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_images');
    }
};