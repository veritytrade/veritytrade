<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Update Users Table
|--------------------------------------------------------------------------
| Adds contact info & approval workflow to users.
| Soft deletes enabled so users can be restored.
|--------------------------------------------------------------------------
*/
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            // Add phone for WhatsApp contact
            $table->string('phone')->nullable()->after('email');

            // Add full address
            $table->text('address')->nullable()->after('phone');

            // Admin approval workflow
            $table->boolean('is_approved')->default(false)->after('remember_token');
            $table->timestamp('approved_at')->nullable()->after('is_approved');

            // Approved by user_id (who approved this user)
            $table->foreignId('approved_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();

            // Soft deletes for users
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'is_approved', 'approved_at']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
            $table->dropSoftDeletes();
        });
    }
};
