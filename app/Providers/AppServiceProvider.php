<?php

namespace App\Providers;

use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!Schema::hasTable('feature_flags')) {
            return;
        }

        $fromAddress = FeatureFlag::value('mail_from_address', config('mail.from.address'));
        $fromName = FeatureFlag::value('mail_from_name', config('mail.from.name'));

        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);
    }
}
