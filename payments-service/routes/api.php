<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Orders API Routes
|--------------------------------------------------------------------------
*/
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/user/{userId}', [OrderController::class, 'userOrders']);

/*
|--------------------------------------------------------------------------
| Payments & Checkout API Routes
|--------------------------------------------------------------------------
*/
Route::post('/payments/checkout', [PaymentController::class, 'checkout']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);
