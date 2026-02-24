<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Categories Table
|--------------------------------------------------------------------------
| Stores top-level categories (Phones, Laptops, etc.)
|--------------------------------------------------------------------------
*/
return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();                       // Primary key
            $table->string('name');             // Category name
            $table->string('slug')->unique();   // URL-friendly slug
            $table->boolean('is_active')->default(true); // Admin activation toggle
            $table->integer('position')->default(0);     // Sorting position
            $table->timestamps();               // Timestamps
            $table->softDeletes();              // Soft deletes enabled
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
