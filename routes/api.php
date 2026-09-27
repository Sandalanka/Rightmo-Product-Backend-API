<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductImageController;
use App\Http\Controllers\Product\ProductRatingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('categories', [CategoryController::class, 'index']);

        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('{productId}', [ProductController::class, 'show'])->whereNumber('productId');
            Route::post('/', [ProductController::class, 'store']);
            Route::put('{productId}', [ProductController::class, 'update'])->whereNumber('productId');
            Route::delete('{productId}', [ProductController::class, 'destroy'])->whereNumber('productId');

            Route::post('{productId}/images', [ProductImageController::class, 'store'])->whereNumber('productId');
            Route::put('{productId}/images/{imageId}', [ProductImageController::class, 'update'])->whereNumber(['productId', 'imageId']);
            Route::delete('{productId}/images/{imageId}', [ProductImageController::class, 'destroy'])->whereNumber(['productId', 'imageId']);

            Route::get('{productId}/ratings', [ProductRatingController::class, 'index'])->whereNumber('productId');
            Route::post('{productId}/ratings', [ProductRatingController::class, 'store'])->whereNumber('productId');
            Route::delete('{productId}/ratings', [ProductRatingController::class, 'destroy'])->whereNumber('productId');
        });
    });
});
