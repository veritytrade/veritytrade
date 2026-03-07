<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Drop helper / pivot tables first (they depend on core product tables).
         */
        if (Schema::hasTable('model_spec_values')) {
            Schema::dropIfExists('model_spec_values');
        }

        if (Schema::hasTable('spec_values')) {
            Schema::dropIfExists('spec_values');
        }

        if (Schema::hasTable('specs')) {
            Schema::dropIfExists('specs');
        }

        if (Schema::hasTable('spec_groups')) {
            Schema::dropIfExists('spec_groups');
        }

        /**
         * Drop legacy request table that referenced the old product domain.
         * Orders / tracking / invoices are kept.
         */
        if (Schema::hasTable('requests')) {
            Schema::dropIfExists('requests');
        }

        /**
         * Drop pricing engine tables.
         */
        if (Schema::hasTable('price_rules')) {
            Schema::dropIfExists('price_rules');
        }

        if (Schema::hasTable('pricing_settings')) {
            Schema::dropIfExists('pricing_settings');
        }

        /**
         * Drop grading / memory catalog tables.
         */
        if (Schema::hasTable('appearance_grades')) {
            Schema::dropIfExists('appearance_grades');
        }

        if (Schema::hasTable('functionality_grades')) {
            Schema::dropIfExists('functionality_grades');
        }

        if (Schema::hasTable('memories')) {
            Schema::dropIfExists('memories');
        }

        /**
         * Drop core catalog hierarchy (categories / brands / series / models).
         */
        if (Schema::hasTable('models')) {
            Schema::dropIfExists('models');
        }

        if (Schema::hasTable('series')) {
            Schema::dropIfExists('series');
        }

        if (Schema::hasTable('brands')) {
            Schema::dropIfExists('brands');
        }

        if (Schema::hasTable('categories')) {
            Schema::dropIfExists('categories');
        }
    }

    public function down(): void
    {
        // This cleanup migration is destructive and is not intended to be rolled back.
        // If you need the old product domain again, restore from backup or re-run the
        // original migrations for categories/brands/series/models/pricing/etc.
    }
};

