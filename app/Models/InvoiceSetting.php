<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    protected $fillable = [
        'company_name',
        'tagline',
        'company_address',
        'company_phone',
        'company_email',
        'logo_path',
        'qr_base_url',
        'copyright',
    ];

    public static function get(): self
    {
        $setting = self::first();
        if (! $setting) {
            $setting = self::create([
                'company_name' => 'Verity Trade Global Limited',
                'company_address' => 'Saki-Ogbooro Road, Saki, Oyo State, Nigeria.',
                'company_phone' => '+2347084117779',
                'company_email' => 'info@veritytrade.ng',
                'qr_base_url' => rtrim(config('app.url', ''), '/') . '/track?code={code}',
                'copyright' => '© Verity Trade Global Limited',
            ]);
        }
        return $setting;
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }
        return asset('storage/' . $this->logo_path);
    }
}
