<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Pricing Settings Table
|--------------------------------------------------------------------------
| Stores exchange rate, logistics, fixed margin.
| MUST NOT be hardcoded.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up()
    {
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();

            $table->decimal('exchange_rate', 10, 2);
            $table->decimal('logistics_cost_cny', 10, 2)->default(250);
            $table->decimal('fixed_margin_ngn', 10, 2);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pricing_settings');
    }
};
