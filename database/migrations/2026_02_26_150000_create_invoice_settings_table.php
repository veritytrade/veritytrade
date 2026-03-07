<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Verity Trade Global Limited');
            $table->string('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('qr_base_url')->nullable()->comment('URL pattern with {code} placeholder for tracking');
            $table->string('copyright')->nullable();
            $table->timestamps();
        });

        DB::table('invoice_settings')->insert([
            'company_name' => 'Verity Trade Global Limited',
            'company_address' => 'Saki-Ogbooro Road, Saki, Oyo State, Nigeria.',
            'company_phone' => '+2347084117779',
            'company_email' => 'info@veritytrade.ng',
            'qr_base_url' => '',
            'copyright' => '© Verity Trade Global Limited',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
