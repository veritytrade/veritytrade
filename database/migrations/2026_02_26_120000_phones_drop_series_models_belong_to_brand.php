<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('phone_models') || !Schema::hasTable('phone_series')) {
            return;
        }

        Schema::table('phone_models', function (Blueprint $table) {
            $table->unsignedBigInteger('phone_brand_id')->nullable()->after('id');
        });

        DB::table('phone_models')->update([
            'phone_brand_id' => DB::raw('(SELECT phone_brand_id FROM phone_series WHERE phone_series.id = phone_models.phone_series_id)'),
        ]);

        Schema::table('phone_models', function (Blueprint $table) {
            $table->dropForeign(['phone_series_id']);
            $table->dropUnique(['phone_series_id', 'slug']);
            $table->dropColumn('phone_series_id');
        });

        Schema::table('phone_models', function (Blueprint $table) {
            $table->foreign('phone_brand_id')->references('id')->on('phone_brands')->cascadeOnDelete();
            $table->unique(['phone_brand_id', 'slug']);
        });

        Schema::dropIfExists('phone_series');
    }

    public function down(): void
    {
        Schema::create('phone_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_brand_id')->constrained('phone_brands')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['phone_brand_id', 'slug']);
        });

        Schema::table('phone_models', function (Blueprint $table) {
            $table->dropUnique(['phone_brand_id', 'slug']);
            $table->dropForeign(['phone_brand_id']);
            $table->foreignId('phone_series_id')->nullable()->after('id')->constrained('phone_series')->cascadeOnDelete();
            $table->unique(['phone_series_id', 'slug']);
        });

        Schema::table('phone_models', function (Blueprint $table) {
            $table->dropColumn('phone_brand_id');
        });
    }
};
