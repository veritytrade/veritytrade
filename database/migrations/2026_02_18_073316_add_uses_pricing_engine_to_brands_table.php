<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Add Uses Pricing Engine To Brands Table
|--------------------------------------------------------------------------
| Controls whether brand uses structured pricing engine.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {

            // Determines whether structured pricing applies
            $table->boolean('uses_pricing_engine')
                  ->default(false)
                  ->after('is_active');

        });
    }

    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('uses_pricing_engine');
        });
    }
};
