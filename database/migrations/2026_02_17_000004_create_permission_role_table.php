<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create permission_role Pivot Table
|--------------------------------------------------------------------------
| Links permissions and roles (many-to-many).
|--------------------------------------------------------------------------
*/
return new class extends Migration
{
    public function up()
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')
                ->constrained()
                ->cascadeOnDelete(); // If permission deleted, unlink roles

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete(); // If role deleted, unlink permissions

            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('permission_role');
    }
};
