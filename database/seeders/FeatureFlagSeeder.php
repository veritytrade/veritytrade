<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            ['key' => 'enable_hot_deals', 'value' => '1', 'group' => 'features', 'description' => 'Enable hot deals tab'],
            ['key' => 'enable_phone_pricing', 'value' => '1', 'group' => 'features', 'description' => 'Enable phone pricing flow'],
            ['key' => 'require_email_verification', 'value' => '0', 'group' => 'auth', 'description' => 'Require email verification before login'],
            ['key' => 'mail_from_address', 'value' => env('MAIL_FROM_ADDRESS', 'noreply@veritytrade.com'), 'group' => 'mail', 'description' => 'Sender email for system messages'],
            ['key' => 'mail_from_name', 'value' => env('MAIL_FROM_NAME', 'VerityTrade'), 'group' => 'mail', 'description' => 'Sender name for system messages'],
            ['key' => 'whatsapp_business_number', 'value' => env('WHATSAPP_BUSINESS_NUMBER', '2347084117779'), 'group' => 'public', 'description' => 'Primary WhatsApp business number'],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(
                ['key' => $flag['key']],
                [
                    'value' => $flag['value'],
                    'group' => $flag['group'],
                    'description' => $flag['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
