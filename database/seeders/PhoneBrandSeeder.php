<?php

namespace Database\Seeders;

use App\Modules\Phones\Models\PhoneBrand;
use Illuminate\Database\Seeder;

class PhoneBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = ['Apple', 'Samsung', 'Xiaomi', 'Google Pixel', 'Huawei', 'Realme', 'Vivo', 'Oppo'];

        foreach ($brands as $name) {
            PhoneBrand::withTrashed()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true, 'deleted_at' => null]
            );
        }
    }
}
