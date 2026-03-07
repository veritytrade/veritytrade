<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('phone_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_brand_id')
                ->constrained('phone_brands')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['phone_brand_id', 'slug']);
        });

        Schema::create('phone_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_series_id')
                ->constrained('phone_series')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->decimal('logistics_cost_cny', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['phone_series_id', 'slug']);
        });

        Schema::create('phone_specs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('phone_spec_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_spec_id')
                ->constrained('phone_specs')
                ->cascadeOnDelete();
            $table->string('value');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['phone_spec_id', 'value']);
        });

        Schema::create('phone_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_model_id')
                ->constrained('phone_models')
                ->cascadeOnDelete();
            $table->decimal('min_price_cny', 12, 2);
            $table->decimal('max_price_cny', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('phone_variant_spec_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_variant_id')
                ->constrained('phone_variants')
                ->cascadeOnDelete();
            $table->foreignId('phone_spec_value_id')
                ->constrained('phone_spec_values')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['phone_variant_id', 'phone_spec_value_id'], 'phone_variant_spec_values_unique');
        });

        Schema::create('phone_pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('exchange_rate', 12, 2);
            $table->unsignedBigInteger('rounding_unit')->default(10000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_pricing_settings');
        Schema::dropIfExists('phone_variant_spec_values');
        Schema::dropIfExists('phone_variants');
        Schema::dropIfExists('phone_spec_values');
        Schema::dropIfExists('phone_specs');
        Schema::dropIfExists('phone_models');
        Schema::dropIfExists('phone_series');
        Schema::dropIfExists('phone_brands');
    }
};

