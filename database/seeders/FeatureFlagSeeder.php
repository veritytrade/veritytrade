<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            ['key' => 'require_email_verification', 'value' => '1', 'group' => 'auth', 'description' => 'Require 6-digit email verification code before login'],
            ['key' => 'require_admin_approval', 'value' => '1', 'group' => 'auth', 'description' => 'Require admin approval before login'],
            ['key' => 'enable_customer_address', 'value' => '0', 'group' => 'profile', 'description' => 'Enable customer address fields in profile/forms'],
            ['key' => 'enable_logistics_update_emails', 'value' => '1', 'group' => 'mail', 'description' => 'Email customers when shipment stage changes or new carrier tracking rows arrive (only while in transit; stops after dispatched)'],
            ['key' => 'mail_from_address', 'value' => env('MAIL_FROM_ADDRESS', ''), 'group' => 'mail', 'description' => 'Sender email for system messages (leave empty to use .env MAIL_FROM_ADDRESS)'],
            ['key' => 'mail_from_name', 'value' => env('MAIL_FROM_NAME', ''), 'group' => 'mail', 'description' => 'Sender name for system messages (leave empty to use .env MAIL_FROM_NAME)'],
            ['key' => 'whatsapp_number', 'value' => env('WHATSAPP_BUSINESS_NUMBER', ''), 'group' => 'public', 'description' => 'Primary WhatsApp business number (leave empty to use .env)'],
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
