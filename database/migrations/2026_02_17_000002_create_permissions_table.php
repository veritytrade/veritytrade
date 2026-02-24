<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Permissions Table
|--------------------------------------------------------------------------
| Permissions represent fine-grained actions like "manage_categories".
|--------------------------------------------------------------------------
*/
return new class extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();                     // Primary Key
            $table->string('name')->unique(); // Permission code
            $table->string('description')->nullable(); // Optional text
            $table->timestamps();             // Timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
