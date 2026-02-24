<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('spec_groups')) {
            Schema::create('spec_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['category_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('specs')) {
            Schema::create('specs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('spec_group_id')->constrained('spec_groups')->cascadeOnDelete();
                $table->string('name');
                $table->enum('input_type', ['dropdown', 'text', 'number'])->default('dropdown');
                $table->boolean('is_required')->default(false);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();

                $table->index(['spec_group_id', 'position']);
            });
        }

        if (!Schema::hasTable('spec_values')) {
            Schema::create('spec_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('spec_id')->constrained('specs')->cascadeOnDelete();
                $table->string('value');
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();

                $table->index(['spec_id', 'position']);
            });
        }

        if (!Schema::hasTable('model_spec_values')) {
            Schema::create('model_spec_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
                $table->foreignId('spec_value_id')->constrained('spec_values')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['model_id', 'spec_value_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('model_spec_values');
        Schema::dropIfExists('spec_values');
        Schema::dropIfExists('specs');
        Schema::dropIfExists('spec_groups');
    }
};
