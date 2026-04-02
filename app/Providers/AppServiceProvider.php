<?php

namespace App\Providers;

use App\Models\FeatureFlag;
use App\Models\Order;
use App\Modules\Phones\Models\PhoneBrand;
use App\Modules\Phones\Models\PhoneModel;
use App\Modules\Phones\Models\PhonePricingSetting;
use App\Modules\Phones\Models\PhoneVariant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Use custom FilesystemManager so uploads work when PHP fileinfo extension is missing
        $this->app->singleton('filesystem', function ($app) {
            return new \App\Filesystem\FilesystemManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Phones module: route model binding for admin.phones.*
        // Admin uses brand ID (e.g. /admin/phones/brands/1/models), frontend uses slug (e.g. /phones/apple)
        Route::bind('brand', function ($value) {
            return is_numeric($value)
                ? PhoneBrand::findOrFail((int) $value)
                : PhoneBrand::where('slug', $value)->firstOrFail();
        });
        Route::bind('model', fn ($value) => PhoneModel::findOrFail($value));
        Route::bind('variant', fn ($value) => PhoneVariant::findOrFail($value));
        Route::bind('setting', fn ($value) => PhonePricingSetting::findOrFail($value));

        // Customer dashboard: scope orders to current user (404 if not theirs)
        Route::bind('order', function ($value) {
            if (request()->routeIs('dashboard.orders.*')) {
                return Order::where('user_id', auth()->id())->findOrFail($value);
            }
            return Order::findOrFail($value);
        });

        // Ensure public disk uses current runtime path (avoids wrong path when config was cached elsewhere)
        Config::set('filesystems.disks.public.root', storage_path('app/public'));

        // Production hardening:
        // Some shared hosting setups have broken/missing DB session tables which causes
        // Laravel CSRF validation to fail and produce a "419 Page Expired" loop.
        // For all admin routes, force file sessions to keep admin login stable.
        if (request()->is('admin', 'admin/*')) {
            Config::set('session.driver', 'file');
        }

        try {
            $fromAddress = FeatureFlag::value('mail_from_address', config('mail.from.address'));
            $fromName = FeatureFlag::value('mail_from_name', config('mail.from.name'));
            if (trim((string) $fromAddress) !== '') {
                Config::set('mail.from.address', $fromAddress);
            }
            if (trim((string) $fromName) !== '') {
                Config::set('mail.from.name', $fromName);
            }
        } catch (\Throwable $e) {
            // Mail config falls back to .env
        }
    }
}
