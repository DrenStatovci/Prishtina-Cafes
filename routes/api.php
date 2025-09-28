<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MockPaymentController;
use App\Http\Controllers\PublicCafeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;


// Public read-only
Route::get('/cafes', [PublicCafeController::class,'cafes']);
Route::get('/cafes/{slug}/branches', [PublicCafeController::class,'branches']);
Route::get('/cafes/{slug}/categories', [PublicCafeController::class,'categories']);
Route::get('/categories/{slug}/products', [PublicCafeController::class,'products']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(\Illuminate\Http\Request $r) => $r->user());

    // Products (policy-protected)
    Route::get('/products', [ProductController::class,'index']);
    Route::post('/products', [ProductController::class,'store']);
    Route::get('/products/{product}', [ProductController::class,'show']);
    Route::match(['put','patch'], '/products/{product}', [ProductController::class,'update']);
    Route::delete('/products/{product}', [ProductController::class,'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class,'index']);
    Route::post('/orders', [OrderController::class,'store']);
    Route::patch('/orders/{order}/status', [OrderController::class,'updateStatus'])
        ->middleware('can:updateStatus,order');

    // Mock payment
    Route::post('/orders/{order}/pay-mock', [MockPaymentController::class,'store'])
        ->middleware('can:pay,order');
});
