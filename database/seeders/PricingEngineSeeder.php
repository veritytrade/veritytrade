<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Memory;
use App\Models\FunctionalityGrade;
use App\Models\AppearanceGrade;

class PricingEngineSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Global Memories
        |--------------------------------------------------------------------------
        */

        $memories = [64, 128, 256, 512, 1024];

        foreach ($memories as $size) {
            Memory::firstOrCreate([
                'size_gb' => $size
            ], [
                'is_active' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Functionality Grades
        |--------------------------------------------------------------------------
        */

        $functionalityGrades = ['S', 'A', 'B', 'C'];

        foreach ($functionalityGrades as $grade) {
            FunctionalityGrade::firstOrCreate([
                'grade' => $grade
            ], [
                'is_active' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Appearance Grades
        |--------------------------------------------------------------------------
        */

        $appearancePercentages = [99, 95, 90, 85, 80];

        foreach ($appearancePercentages as $percent) {
            AppearanceGrade::firstOrCreate([
                'percentage' => $percent
            ], [
                'is_active' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DO NOT seed pricing_settings
        |--------------------------------------------------------------------------
        | Exchange rate & logistics must be configured per brand
        | from admin panel only.
        */
    }
}