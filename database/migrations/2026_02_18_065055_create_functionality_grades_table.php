<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Functionality Grades Table
|--------------------------------------------------------------------------
| S / A / B / C
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('functionality_grades', function (Blueprint $table) {
            $table->id();

            // Example: S, A, B, C
            $table->string('grade')->unique();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('functionality_grades');
    }
};
