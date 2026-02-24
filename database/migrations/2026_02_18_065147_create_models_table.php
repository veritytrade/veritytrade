<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Models Table
|--------------------------------------------------------------------------
| Example: iPhone 17 Pro Max
| Belongs to Series
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')
                ->constrained('series')
                ->cascadeOnDelete();

            $table->string('name');

            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['series_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('models');
    }
};
