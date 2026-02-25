<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Admin\ModelController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PhoneController;
use App\Http\Controllers\Public\CategoryRequestController;
use App\Http\Controllers\Public\CategoryBrowseController;
use App\Http\Controllers\Public\RequestHubController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Admin\SpecificationController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// ✅ FIXED: Single homepage route (removed duplicate)
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('phones')->name('public.phones.')->group(function () {
    Route::get('/', [PhoneController::class, 'index'])->name('brands');
    Route::get('/{brandUuid}', [PhoneController::class, 'brand'])->name('brand');
    Route::get('/{brandUuid}/device/{deviceUuid}', [PhoneController::class, 'device'])->name('device');
    Route::get('/{brandUuid}/request', [PhoneController::class, 'requestForm'])->name('request.form');
    Route::post('/{brandUuid}/request', [PhoneController::class, 'request'])->name('request');
    Route::post('/device/{deviceUuid}/whatsapp', [PhoneController::class, 'whatsapp'])->name('whatsapp');
});

// WhatsApp redirect for Hot Deals
Route::get('/deal/{deal:uuid}/whatsapp', [LandingController::class, 'whatsapp'])
    ->name('deal.whatsapp');

Route::get('/request', [RequestHubController::class, 'index'])->name('public.request-hub');

Route::prefix('categories')->name('public.categories.')->group(function () {
    Route::get('/{categorySlug}', [CategoryBrowseController::class, 'show'])->name('show');
    Route::get('/{categorySlug}/brands/{brandUuid}', [CategoryBrowseController::class, 'brand'])->name('brand');
    Route::get('/{categorySlug}/request', [CategoryRequestController::class, 'form'])->name('request.form');
    Route::post('/{categorySlug}/request', [CategoryRequestController::class, 'submit'])->name('request.submit');
});

/*
|--------------------------------------------------------------------------
| Breeze Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Customer Dashboard
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard')
    ->middleware(['auth', 'approved'])
    ->group(function () {
        Route::get('/', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [CustomerDashboardController::class, 'orders'])->name('dashboard.orders');
        Route::get('/tracking', [CustomerDashboardController::class, 'tracking'])->name('dashboard.tracking');
        Route::get('/invoices', [CustomerDashboardController::class, 'invoices'])->name('dashboard.invoices');
    });

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Auth
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('admin.login');

    Route::post('/login', [AdminLoginController::class, 'login'])
        ->name('admin.login.submit');

    Route::post('/logout', [AdminLoginController::class, 'logout'])
        ->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| Protected Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'approved', 'role:super_admin,admin,staff'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware('permission:view_dashboard')
            ->name('admin.dashboard');

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:manage_categories')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])
            ->name('admin.categories.index');

        Route::post('/categories', [CategoryController::class, 'store'])
            ->name('admin.categories.store');

        Route::post('/categories/{category}', [CategoryController::class, 'update'])
            ->name('admin.categories.update');

        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
            ->name('admin.categories.destroy');

        Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggle'])
            ->name('admin.categories.toggle');

        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])
            ->name('admin.categories.edit');

        Route::get('/specs', [SpecificationController::class, 'index'])
            ->name('admin.specs.index');

        Route::post('/specs/groups', [SpecificationController::class, 'storeGroup'])
            ->name('admin.specs.groups.store');

        Route::post('/specs/groups/{group}/toggle', [SpecificationController::class, 'toggleGroup'])
            ->name('admin.specs.groups.toggle');

        Route::post('/specs/groups/{group}/specs', [SpecificationController::class, 'storeSpec'])
            ->name('admin.specs.store');

        Route::post('/specs/specs/{spec}/values', [SpecificationController::class, 'storeValue'])
            ->name('admin.specs.values.store');
        });

        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:manage_brands')->group(function () {
        Route::get('/brands', [\App\Http\Controllers\Admin\BrandController::class, 'index'])
            ->name('admin.brands.index');

        Route::post('/brands', [\App\Http\Controllers\Admin\BrandController::class, 'store'])
            ->name('admin.brands.store');

        Route::get('/brands/{brand}/edit', [\App\Http\Controllers\Admin\BrandController::class, 'edit'])
            ->name('admin.brands.edit');

        Route::post('/brands/{brand}', [\App\Http\Controllers\Admin\BrandController::class, 'update'])
            ->name('admin.brands.update');

        Route::delete('/brands/{brand}', [\App\Http\Controllers\Admin\BrandController::class, 'destroy'])
            ->name('admin.brands.destroy');

        Route::post('/brands/{brand}/toggle', [\App\Http\Controllers\Admin\BrandController::class, 'toggle'])
            ->name('admin.brands.toggle');
        });

        /*
        |--------------------------------------------------------------------------
        | Series
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:manage_series')->group(function () {
        Route::get('/series', [SeriesController::class, 'index'])
            ->name('admin.series.index');

        Route::post('/series', [SeriesController::class, 'store'])
            ->name('admin.series.store');

        Route::get('/series/{series}/edit', [SeriesController::class, 'edit'])
            ->name('admin.series.edit');

        Route::post('/series/{series}', [SeriesController::class, 'update'])
            ->name('admin.series.update');

        Route::delete('/series/{series}', [SeriesController::class, 'destroy'])
            ->name('admin.series.destroy');

        Route::post('/series/{series}/toggle', [SeriesController::class, 'toggle'])
            ->name('admin.series.toggle');
        });

        /*
        |--------------------------------------------------------------------------
        | Models
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:manage_models')->group(function () {
        Route::get('/models', [ModelController::class, 'hub'])
            ->name('admin.models.hub');

        Route::get('/series/{series}/models', [ModelController::class, 'index'])
            ->name('admin.models.index');

        Route::post('/series/{series}/models', [ModelController::class, 'store'])
            ->name('admin.models.store');

        Route::get('/brands/{brand}/models', [ModelController::class, 'brandIndex'])
            ->name('admin.brand-models.index');

        Route::post('/brands/{brand}/models', [ModelController::class, 'storeByBrand'])
            ->name('admin.brand-models.store');

        Route::post('/models/{model}/toggle', [ModelController::class, 'toggle'])
            ->name('admin.models.toggle');

        Route::delete('/models/{model}', [ModelController::class, 'destroy'])
            ->name('admin.models.destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Pricing Engine
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:access_pricing_engine')->group(function () {
        Route::get('/pricing', [\App\Http\Controllers\Admin\PricingController::class, 'index'])
            ->name('admin.pricing.index');

        Route::post('/pricing', [\App\Http\Controllers\Admin\PricingController::class, 'store'])
            ->name('admin.pricing.store');

        Route::post('/pricing/{priceRule}/toggle', [\App\Http\Controllers\Admin\PricingController::class, 'toggle'])
            ->name('admin.pricing.toggle');

        Route::delete('/pricing/{priceRule}', [\App\Http\Controllers\Admin\PricingController::class, 'destroy'])
            ->name('admin.pricing.destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Pricing Settings
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:access_pricing_settings')->group(function () {
        Route::get('/pricing/settings', [\App\Http\Controllers\Admin\PricingSettingsController::class, 'index'])
            ->name('admin.pricing.settings');

        Route::post('/pricing/settings', [\App\Http\Controllers\Admin\PricingSettingsController::class, 'store'])
            ->name('admin.pricing.settings.store');

        Route::post('/pricing/settings/{pricingSetting}/toggle', [\App\Http\Controllers\Admin\PricingSettingsController::class, 'toggle'])
            ->name('admin.pricing.settings.toggle');

        Route::delete('/pricing/settings/{pricingSetting}', [\App\Http\Controllers\Admin\PricingSettingsController::class, 'destroy'])
            ->name('admin.pricing.settings.destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Site Settings
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:manage_feature_flags')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Admin\SiteSettingsController::class, 'index'])
            ->name('admin.settings.index');

        Route::put('/settings/{setting}', [\App\Http\Controllers\Admin\SiteSettingsController::class, 'update'])
            ->name('admin.settings.update');
        });

        /*
        |--------------------------------------------------------------------------
        | Hot Deals Management
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:manage_deals')->group(function () {
        Route::get('/deals', [\App\Http\Controllers\Admin\DealController::class, 'index'])
            ->name('admin.deals.index');

        Route::get('/deals/create', [\App\Http\Controllers\Admin\DealController::class, 'create'])
            ->name('admin.deals.create');

        Route::post('/deals', [\App\Http\Controllers\Admin\DealController::class, 'store'])
            ->name('admin.deals.store');

        Route::get('/deals/{deal}/edit', [\App\Http\Controllers\Admin\DealController::class, 'edit'])
            ->name('admin.deals.edit');

        // ✅ Use Route::match to accept BOTH PUT and PATCH
        Route::match(['put', 'patch'], '/deals/{deal}', [\App\Http\Controllers\Admin\DealController::class, 'update'])
            ->name('admin.deals.update');

        Route::delete('/deals/{deal}', [\App\Http\Controllers\Admin\DealController::class, 'destroy'])
            ->name('admin.deals.destroy');

        Route::post('/deals/{deal}/toggle', [\App\Http\Controllers\Admin\DealController::class, 'toggle'])
            ->name('admin.deals.toggle');

        Route::delete('/deals/image/{image}', [\App\Http\Controllers\Admin\DealController::class, 'deleteImage'])
            ->name('admin.deals.image.destroy');
        });

        Route::middleware('permission:approve_users')->group(function () {
            Route::get('/registered-users', [\App\Http\Controllers\Admin\UserManagementController::class, 'registeredUsers'])
                ->name('admin.registered-users.index');

            Route::post('/registered-users/{user}/approve', [\App\Http\Controllers\Admin\UserManagementController::class, 'approve'])
                ->name('admin.registered-users.approve');

            Route::delete('/registered-users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])
                ->name('admin.registered-users.destroy');
        });

        Route::middleware('permission:assign_roles')->group(function () {
            Route::get('/staff', [\App\Http\Controllers\Admin\UserManagementController::class, 'staffIndex'])
                ->name('admin.staff.index');

            Route::post('/staff/assign-role', [\App\Http\Controllers\Admin\UserManagementController::class, 'assignRoleByEmail'])
                ->name('admin.staff.assign-role');

            Route::post('/staff/{user}/remove-role', [\App\Http\Controllers\Admin\UserManagementController::class, 'removeRole'])
                ->name('admin.staff.remove-role');
        });

        Route::middleware('permission:manage_feature_flags')->group(function () {
            Route::get('/feature-flags', [\App\Http\Controllers\Admin\FeatureFlagController::class, 'index'])
                ->name('admin.feature-flags.index');

            Route::put('/feature-flags/{featureFlag}', [\App\Http\Controllers\Admin\FeatureFlagController::class, 'update'])
                ->name('admin.feature-flags.update');
        });

        Route::middleware('permission:assign_roles')->group(function () {
            Route::get('/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'index'])
                ->name('admin.roles.index');

            Route::put('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'update'])
                ->name('admin.roles.permissions.update');
        });
    });
