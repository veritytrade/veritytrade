<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Phones\Controllers\Admin\PhoneBrandController;
use App\Modules\Phones\Controllers\Admin\PhoneModelController;
use App\Modules\Phones\Controllers\Admin\PhoneVariantController;
use App\Modules\Phones\Controllers\Admin\PhonePricingSettingsController;

Route::prefix('phones')
    ->name('admin.phones.')
    ->group(function () {
        // Brands
        Route::get('/brands', [PhoneBrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [PhoneBrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [PhoneBrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [PhoneBrandController::class, 'edit'])->name('brands.edit');
        Route::match(['put', 'patch'], '/brands/{brand}', [PhoneBrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [PhoneBrandController::class, 'destroy'])->name('brands.destroy');

        // Models (under brand)
        Route::get('/brands/{brand}/models', [PhoneModelController::class, 'index'])->name('models.index');
        Route::get('/brands/{brand}/models/create', [PhoneModelController::class, 'create'])->name('models.create');
        Route::post('/brands/{brand}/models', [PhoneModelController::class, 'store'])->name('models.store');
        Route::get('/models/{model}/edit', [PhoneModelController::class, 'edit'])->name('models.edit');
        Route::match(['put', 'patch'], '/models/{model}', [PhoneModelController::class, 'update'])->name('models.update');
        Route::delete('/models/{model}', [PhoneModelController::class, 'destroy'])->name('models.destroy');

        // Variants: index only (view); create/edit done on model form
        Route::get('/models/{model}/variants', [PhoneVariantController::class, 'index'])->name('variants.index');
        Route::delete('/variants/{variant}', [PhoneVariantController::class, 'destroy'])->name('variants.destroy');

        // Pricing settings (global)
        Route::get('/pricing-settings', [PhonePricingSettingsController::class, 'index'])->name('pricing-settings.index');
        Route::get('/pricing-settings/create', [PhonePricingSettingsController::class, 'create'])->name('pricing-settings.create');
        Route::post('/pricing-settings', [PhonePricingSettingsController::class, 'store'])->name('pricing-settings.store');
        Route::get('/pricing-settings/{setting}/edit', [PhonePricingSettingsController::class, 'edit'])->name('pricing-settings.edit');
        Route::match(['put', 'patch'], '/pricing-settings/{setting}', [PhonePricingSettingsController::class, 'update'])->name('pricing-settings.update');
        Route::delete('/pricing-settings/{setting}', [PhonePricingSettingsController::class, 'destroy'])->name('pricing-settings.destroy');
    });
