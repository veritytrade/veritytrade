<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (! Schema::hasColumn('deals', 'source_product_id')) {
                $table->unsignedBigInteger('source_product_id')->nullable()->after('created_by');
                $table->index('source_product_id');
                $table->unique('source_product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 'source_product_id')) {
                $table->dropUnique(['source_product_id']);
                $table->dropIndex(['source_product_id']);
                $table->dropColumn('source_product_id');
            }
        });
    }
};
