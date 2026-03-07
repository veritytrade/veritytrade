<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Phones\Controllers\Frontend\PhoneBrowseController;

Route::prefix('phones')
    ->name('phones.')
    ->group(function () {
        Route::get('/', [PhoneBrowseController::class, 'index'])->name('index');
        Route::get('/{brandSlug}', [PhoneBrowseController::class, 'brand'])->name('brand');
        Route::get('/{brandSlug}/{modelSlug}', [PhoneBrowseController::class, 'model'])->name('model');
    });
