<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_hero', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_headline')->nullable();
            $table->string('hero_subheadline')->nullable();
            $table->string('hero_cta_text')->nullable();
            $table->string('hero_cta_url')->nullable();
            $table->boolean('hero_visible')->default(true);
            $table->timestamps();
        });

        DB::table('homepage_hero')->insert([
            'hero_headline' => 'Source Quality Phones Direct From China',
            'hero_cta_text' => 'Browse Deals',
            'hero_cta_url' => '#hot-deals',
            'hero_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_hero');
    }
};
