<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Phones\Models\PhoneSpec;
use App\Modules\Phones\Models\PhoneSpecValue;
use App\Modules\Phones\Models\PhonePricingSetting;

class PhoneSpecSeeder extends Seeder
{
    public function run(): void
    {
        // Order: Storage (1), Appearance (2), Function (3)
        $storage = PhoneSpec::firstOrCreate(
            ['name' => 'Storage'],
            ['position' => 1, 'is_active' => true]
        );

        $appearance = PhoneSpec::firstOrCreate(
            ['name' => 'Appearance'],
            ['position' => 2, 'is_active' => true]
        );

        $function = PhoneSpec::firstOrCreate(
            ['name' => 'Function'],
            ['position' => 3, 'is_active' => true]
        );

        foreach (['64GB', '128GB', '256GB', '512GB', '1TB'] as $index => $value) {
            PhoneSpecValue::firstOrCreate(
                ['phone_spec_id' => $storage->id, 'value' => $value],
                ['position' => $index + 1, 'is_active' => true]
            );
        }

        foreach (['99%', '95%', '90%', '80%'] as $index => $value) {
            PhoneSpecValue::firstOrCreate(
                ['phone_spec_id' => $appearance->id, 'value' => $value],
                ['position' => $index + 1, 'is_active' => true]
            );
        }

        foreach (['S', 'A', 'B', 'C'] as $index => $value) {
            PhoneSpecValue::firstOrCreate(
                ['phone_spec_id' => $function->id, 'value' => $value],
                ['position' => $index + 1, 'is_active' => true]
            );
        }

        if (!PhonePricingSetting::where('is_active', true)->exists()) {
            PhonePricingSetting::create([
                'exchange_rate' => 220,
                'logistics_cny' => 250,
                'rounding_unit' => 10000,
                'profit_margin_ngn' => 10000,
                'is_active' => true,
            ]);
        }
    }
}

