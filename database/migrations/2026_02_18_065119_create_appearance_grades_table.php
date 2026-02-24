<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Appearance Grades Table
|--------------------------------------------------------------------------
| 99%, 95%, 90%, 85%, etc.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('appearance_grades', function (Blueprint $table) {
            $table->id();

            // Percentage (99, 95, 90, etc.)
            $table->integer('percentage');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('percentage');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appearance_grades');
    }
};
