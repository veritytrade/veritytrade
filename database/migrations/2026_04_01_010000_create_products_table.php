<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('price_ngn');
            $table->longText('description_en')->nullable();
            $table->json('specs_json')->nullable();
            $table->text('condition_notes')->nullable();
            $table->string('status', 32)->default('draft');
            $table->unsignedInteger('stock')->default(1);

            // Source tracking fields for private ingestion provenance.
            $table->string('source_site', 100);
            $table->string('source_item_id', 191);
            $table->text('source_url_private')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['source_site', 'source_item_id']);
            $table->unique(['source_site', 'source_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
