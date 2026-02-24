<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Series Table (Product Lines)
|--------------------------------------------------------------------------
| Stores generation-level grouping under a brand.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('series', function (Blueprint $table) {

            $table->id();

            // Belongs to Brand
            $table->foreignId('brand_id')
                ->constrained()
                ->cascadeOnDelete();

            // Clean name only (e.g., 14 Series, EliteBook)
            $table->string('name');

            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Unique per brand (including deleted_at for soft delete compatibility)
            $table->unique(['brand_id', 'name', 'deleted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('series');
    }
};
