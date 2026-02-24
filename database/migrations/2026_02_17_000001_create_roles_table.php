<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Roles Table
|--------------------------------------------------------------------------
| Roles define user types like super_admin, admin, staff, customer.
| A user can have multiple roles.
|--------------------------------------------------------------------------
*/
return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();                // Primary Key
            $table->string('name')->unique();  // e.g., admin, staff
            $table->string('description')->nullable(); // Optional description
            $table->timestamps();        // Created & modified times
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
