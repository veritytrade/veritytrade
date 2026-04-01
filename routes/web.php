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
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

/*
|--------------------------------------------------------------------------
| Secure storage file serving (/_f/*) – no symlink required; path & extension restricted
|--------------------------------------------------------------------------
*/
Route::get('/_f/{path}', function (string $path) {
    $path = str_replace(['..', '\\', "\0"], ['', '/', ''], $path);
    $path = trim($path, '/');
    if ($path === '') {
        abort(404);
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'pdf', 'ico'];
    $isLegacyProductsBin = ($ext === 'bin') && str_starts_with($path, 'products/');
    if (! in_array($ext, $allowed, true) && ! $isLegacyProductsBin) {
        abort(404);
    }

    // Use base_path so storage root is correct when config was cached elsewhere
    $root = base_path('storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public');
    $fullPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    $real = @realpath($fullPath);
    if ($real === false || ! is_file($real)) {
        abort(404);
    }
    $rootReal = realpath($root);
    if ($rootReal === false || ! str_starts_with($real, $rootReal)) {
        abort(404);
    }
    $sep = DIRECTORY_SEPARATOR;
    if (strlen($real) > strlen($rootReal) && ! in_array($real[strlen($rootReal)], [$sep, '/', '\\'], true)) {
        abort(404);
    }

    $mime = match ($ext) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'pdf' => 'application/pdf',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        default => 'application/octet-stream',
    };

    // Backward compatibility for early ingested image files saved as .bin.
    if ($isLegacyProductsBin) {
        $signature = @file_get_contents($real, false, null, 0, 16);
        if ($signature === false || $signature === '') {
            abort(404);
        }
        if (str_starts_with($signature, "\xFF\xD8\xFF")) {
            $mime = 'image/jpeg';
        } elseif (str_starts_with($signature, "\x89PNG\x0D\x0A\x1A\x0A")) {
            $mime = 'image/png';
        } elseif (substr($signature, 0, 4) === 'RIFF' && substr($signature, 8, 4) === 'WEBP') {
            $mime = 'image/webp';
        } elseif (strlen($signature) >= 12 && substr($signature, 4, 8) === 'ftypavif') {
            $mime = 'image/avif';
        } else {
            abort(404);
        }
    }

    return response()->file($real, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=86400',
        'X-Content-Type-Options' => 'nosniff',
    ]);
})->where('path', '.*')->name('storage.serve');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/landing', [LandingController::class, 'index'])->name('landing');

// WhatsApp redirect for Hot Deals
Route::get('/deal/{deal:uuid}/whatsapp', [LandingController::class, 'whatsapp'])
    ->name('deal.whatsapp');

// Deal detail page (used by tapping deal cards on the home page)
Route::get('/deal/{deal:uuid}', [LandingController::class, 'show'])
    ->name('deal.show');

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
        Route::get('/orders/create', [\App\Http\Controllers\Customer\OrderController::class, 'create'])->name('dashboard.orders.create');
        Route::post('/orders', [\App\Http\Controllers\Customer\OrderController::class, 'store'])->name('dashboard.orders.store');
        Route::get('/orders/{order}/edit', [\App\Http\Controllers\Customer\OrderController::class, 'edit'])->name('dashboard.orders.edit');
        Route::put('/orders/{order}', [\App\Http\Controllers\Customer\OrderController::class, 'update'])->name('dashboard.orders.update');
        Route::delete('/orders/{order}', [\App\Http\Controllers\Customer\OrderController::class, 'destroy'])->name('dashboard.orders.destroy');
        Route::post('/orders/{order}/confirm-delivery', [CustomerDashboardController::class, 'confirmDelivery'])->name('dashboard.orders.confirm-delivery');
        Route::post('/orders/{order}/request-invoice', [CustomerDashboardController::class, 'requestInvoice'])->name('dashboard.orders.request-invoice');
        Route::get('/tracking', [CustomerDashboardController::class, 'tracking'])->name('dashboard.tracking');
        Route::get('/invoices', [CustomerDashboardController::class, 'invoices'])->name('dashboard.invoices');
        Route::get('/invoices/{id}/download', [CustomerDashboardController::class, 'downloadInvoice'])->name('dashboard.invoices.download')->whereNumber('id');
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

    Route::post('/profile/delete-otp', [ProfileController::class, 'sendDeleteOtp'])
        ->middleware('throttle:3,1')
        ->name('profile.delete-otp.send');
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
        ->middleware('throttle:5,1')
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
        | Homepage Hero (images & content)
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:view_dashboard')->group(function () {
            Route::get('/homepage-hero', [\App\Http\Controllers\Admin\HomepageHeroController::class, 'edit'])->name('admin.homepage-hero.edit');
            Route::match(['put', 'patch'], '/homepage-hero', [\App\Http\Controllers\Admin\HomepageHeroController::class, 'update'])->name('admin.homepage-hero.update');
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

        Route::middleware('permission:generate_invoices')->group(function () {
            Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceSettingsController::class, 'index'])->name('admin.invoices.index');
            Route::get('/invoice-settings', [\App\Http\Controllers\Admin\InvoiceSettingsController::class, 'edit'])->name('admin.invoice-settings.edit');
            Route::get('/invoice-settings/preview', [\App\Http\Controllers\Admin\InvoiceSettingsController::class, 'preview'])->name('admin.invoice-settings.preview');
            Route::get('/invoice-settings/preview-html', [\App\Http\Controllers\Admin\InvoiceSettingsController::class, 'previewHtml'])->name('admin.invoice-settings.preview-html');
            Route::get('/invoices/generate', [\App\Http\Controllers\Admin\InvoiceSettingsController::class, 'generateIndex'])->name('admin.invoice-settings.generate');
            Route::post('/invoices/generate', [\App\Http\Controllers\Admin\InvoiceSettingsController::class, 'generateForShipment'])->name('admin.invoice-settings.generate-for-shipment');
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

        Route::delete('/deals/{deal}/images/{imageId}', [\App\Http\Controllers\Admin\DealController::class, 'deleteImage'])
            ->name('admin.deals.image.destroy');

        // Ingested products review/approval workflow
        Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])
            ->name('admin.products.index');
        Route::get('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'show'])
            ->name('admin.products.show');
        Route::get('/products/{product}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])
            ->name('admin.products.edit');
        Route::put('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])
            ->name('admin.products.update');
        Route::post('/products/{product}/approve', [\App\Http\Controllers\Admin\ProductController::class, 'approve'])
            ->name('admin.products.approve');
        Route::post('/products/{product}/archive', [\App\Http\Controllers\Admin\ProductController::class, 'archive'])
            ->name('admin.products.archive');
        Route::delete('/products/{product}/images/{image}', [\App\Http\Controllers\Admin\ProductController::class, 'deleteImage'])
            ->name('admin.products.images.destroy');
        Route::delete('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])
            ->name('admin.products.destroy');
        });

        Route::middleware('permission:approve_users')->group(function () {
            Route::get('/registered-users', [\App\Http\Controllers\Admin\UserManagementController::class, 'registeredUsers'])
                ->name('admin.registered-users.index');

            Route::get('/customers/360', [\App\Http\Controllers\Admin\UserManagementController::class, 'customer360'])
                ->name('admin.customers.show');

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

        // Global admin quick search (customers, orders, shipments, invoices)
        Route::post('/search', \App\Http\Controllers\Admin\AdminSearchController::class)
            ->name('admin.search');

        // Shipments & Orders (tracking) - create routes MUST come before {id} routes
        Route::middleware('permission:view_tracking')->group(function () {
            Route::get('/shipments', [\App\Http\Controllers\Admin\ShipmentController::class, 'index'])->name('admin.shipments.index');
        });
        Route::middleware('permission:create_shipment')->group(function () {
            Route::get('/shipments/create', [\App\Http\Controllers\Admin\ShipmentController::class, 'create'])->name('admin.shipments.create');
            Route::post('/shipments', [\App\Http\Controllers\Admin\ShipmentController::class, 'store'])->name('admin.shipments.store');
        });
        Route::middleware('permission:view_tracking')->group(function () {
            Route::get('/shipments/{shipment}', [\App\Http\Controllers\Admin\ShipmentController::class, 'show'])->name('admin.shipments.show');
        });
        Route::middleware('permission:update_shipment_stage')->group(function () {
            Route::get('/shipments/{shipment}/edit', [\App\Http\Controllers\Admin\ShipmentController::class, 'edit'])->name('admin.shipments.edit');
            Route::put('/shipments/{shipment}', [\App\Http\Controllers\Admin\ShipmentController::class, 'update'])->name('admin.shipments.update');
            Route::post('/shipments/{shipment}/stage', [\App\Http\Controllers\Admin\ShipmentController::class, 'updateStage'])->name('admin.shipments.update-stage');
            Route::post('/shipments/{shipment}/apply-stage-all', [\App\Http\Controllers\Admin\ShipmentController::class, 'applyStageToAllOrders'])->name('admin.shipments.apply-stage-all');
            Route::post('/shipments/{shipment}/refresh-carrier-tracking', [\App\Http\Controllers\Admin\ShipmentController::class, 'refreshCarrierTracking'])->name('admin.shipments.refresh-carrier-tracking');
            Route::post('/shipments/{shipment}/mark-agent-collected', [\App\Http\Controllers\Admin\ShipmentController::class, 'markAgentCollected'])->name('admin.shipments.mark-agent-collected');
        });

        Route::middleware('permission:view_tracking')->group(function () {
            Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders.index');
        });
        Route::middleware('permission:create_order')->group(function () {
            Route::get('/orders/create', [\App\Http\Controllers\Admin\OrderController::class, 'create'])->name('admin.orders.create');
            Route::post('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'store'])->name('admin.orders.store');
        });
        Route::middleware('permission:view_tracking')->group(function () {
            Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('admin.orders.show');
            Route::get('/orders/{order}/invoice-download', [\App\Http\Controllers\Admin\OrderController::class, 'downloadInvoice'])->name('admin.orders.invoice-download');
            Route::post('/orders/{order}/approve', [\App\Http\Controllers\Admin\OrderController::class, 'approve'])->name('admin.orders.approve')->middleware('permission:approve_orders');
            Route::post('/orders/{order}/generate-invoice', [\App\Http\Controllers\Admin\OrderController::class, 'generateInvoice'])->name('admin.orders.generate-invoice')->middleware('permission:generate_invoices');
        });
        Route::middleware('permission:assign_shipment')->group(function () {
            Route::get('/orders/{order}/edit', [\App\Http\Controllers\Admin\OrderController::class, 'edit'])->name('admin.orders.edit');
            Route::put('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'update'])->name('admin.orders.update');
            Route::post('/orders/{order}/assign-shipment', [\App\Http\Controllers\Admin\OrderController::class, 'assignShipment'])->name('admin.orders.assign-shipment');
            Route::post('/orders/{order}/supplier-mapping', [\App\Http\Controllers\Admin\OrderController::class, 'updateSupplierMapping'])->name('admin.orders.supplier-mapping.update');
        });
        Route::middleware('permission:override_order_stage')->group(function () {
            Route::post('/orders/{order}/override-stage', [\App\Http\Controllers\Admin\OrderController::class, 'overrideStage'])->name('admin.orders.override-stage');
        });
        Route::middleware('permission:view_tracking')->group(function () {
            Route::delete('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'destroy'])->name('admin.orders.destroy');
        });

        // Phones module (admin)
        $phonesAdminRoutes = base_path('app/Modules/Phones/routes_admin.php');
        if (file_exists($phonesAdminRoutes)) {
            require $phonesAdminRoutes;
        }
    });

// Phones module (public)
$phonesWebRoutes = base_path('app/Modules/Phones/routes_web.php');
if (file_exists($phonesWebRoutes)) {
    require $phonesWebRoutes;
}
