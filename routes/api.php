<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);

Route::get(
    'products/{product}/prices',
    [ProductController::class, 'indexPrices']
);

Route::post(
    'products/{product}/prices',
    [ProductController::class, 'storePrice']
);
