<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Brands Table
|--------------------------------------------------------------------------
| Stores brands linked to categories (e.g., iPhone under Phones).
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {

            $table->id();

            // Foreign key to categories
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Scoped uniqueness: brand name unique per category
            $table->unique(['category_id','name','deleted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('brands');
    }
};
