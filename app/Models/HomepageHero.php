<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageHero extends Model
{
    protected $table = 'homepage_hero';

    protected $fillable = [
        'hero_image_path',
        'hero_headline',
        'hero_subheadline',
        'hero_cta_text',
        'hero_cta_url',
        'hero_visible',
    ];

    protected $casts = [
        'hero_visible' => 'boolean',
    ];

    public static function get(): ?self
    {
        return self::first();
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->hero_image_path
            ? storage_asset($this->hero_image_path)
            : null;
    }
}
