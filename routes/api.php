<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MockPaymentController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/orders/{order}/pay-mock', [MockPaymentController::class, 'store'])
    ->middleware(['auth:sanctum']);
