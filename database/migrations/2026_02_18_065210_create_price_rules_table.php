<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Price Rules Table
|--------------------------------------------------------------------------
| Stores ONLY valid combinations.
| Locks combination strictly.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('model_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('memory_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('functionality_grade_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('appearance_grade_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('min_price_cny', 10, 2);
            $table->decimal('max_price_cny', 10, 2);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // LOCK combination strictly
            $table->unique([
                'model_id',
                'memory_id',
                'functionality_grade_id',
                'appearance_grade_id'
            ], 'unique_price_combination');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_rules');
    }
};
