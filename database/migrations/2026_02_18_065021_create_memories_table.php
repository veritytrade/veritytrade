<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Memories Table
|--------------------------------------------------------------------------
| Global memory options (64GB, 128GB, 256GB, etc.)
| Used for all models.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('memories', function (Blueprint $table) {
            $table->id();

            // Memory size in GB (64, 128, 256, 512, 1024)
            $table->integer('size_gb');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent duplicate memory sizes
            $table->unique('size_gb');
        });
    }

    public function down()
    {
        Schema::dropIfExists('memories');
    }
};
