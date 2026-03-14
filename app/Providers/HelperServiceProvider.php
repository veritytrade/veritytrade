<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (file_exists(app_path('Helpers/site_settings.php'))) {
            require_once app_path('Helpers/site_settings.php');
        }
        if (file_exists(app_path('Helpers/storage_asset.php'))) {
            require_once app_path('Helpers/storage_asset.php');
        }
    }
}