<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create role_user Pivot Table
|--------------------------------------------------------------------------
| This table links users and roles (many-to-many).
|--------------------------------------------------------------------------
*/
return new class extends Migration
{
    public function up()
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(); // If user deleted, unlink roles

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete(); // If role deleted, unlink users

            $table->primary(['user_id', 'role_id']); // Composite PK
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
    }
};
