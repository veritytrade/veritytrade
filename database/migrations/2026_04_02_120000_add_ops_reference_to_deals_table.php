<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (! Schema::hasColumn('deals', 'ops_reference')) {
                $table->string('ops_reference', 32)->nullable()->after('source_product_id');
            }
        });

        if (Schema::hasColumn('deals', 'ops_reference')) {
            foreach (DB::table('deals')->select('id', 'source_product_id', 'ops_reference')->orderBy('id')->cursor() as $row) {
                if (filled($row->ops_reference)) {
                    continue;
                }
                $ref = ! empty($row->source_product_id)
                    ? 'VTP' . (int) $row->source_product_id
                    : 'VTD' . (int) $row->id;
                DB::table('deals')->where('id', $row->id)->update(['ops_reference' => $ref]);
            }

            Schema::table('deals', function (Blueprint $table) {
                $table->unique('ops_reference');
            });
        }
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 'ops_reference')) {
                $table->dropUnique(['ops_reference']);
                $table->dropColumn('ops_reference');
            }
        });
    }
};
