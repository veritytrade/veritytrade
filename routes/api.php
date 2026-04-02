<?php

use App\Http\Controllers\Api\Ingestion\ProductImageIngestionController;
use App\Http\Controllers\Api\Ingestion\ProductIngestionController;
use App\Http\Controllers\Api\Public\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('ingestion')
    ->middleware('ingestion.key')
    ->group(function () {
        Route::post('/products', [ProductIngestionController::class, 'store']);
        Route::post('/products/{product}/images', [ProductImageIngestionController::class, 'store']);
    });

Route::prefix('products')
    ->middleware('ingestion.key')
    ->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'show']);
    });
