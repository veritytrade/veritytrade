<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'verity_tracking_code')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique(['verity_tracking_code']);
                $table->dropColumn('verity_tracking_code');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('orders', 'verity_tracking_code')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('verity_tracking_code')->nullable()->unique()->after('tracking_code');
            });
        }
    }
};
