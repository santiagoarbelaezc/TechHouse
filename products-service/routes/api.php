<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Catalog Routes
|--------------------------------------------------------------------------
*/
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Product Management Routes (Admin / Authorized)
|--------------------------------------------------------------------------
*/
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Inter-Service Internal Routes (Protected by Service Token)
| Called by payments-service, assistant-service, etc.
|--------------------------------------------------------------------------
*/
Route::middleware('service.auth')->group(function () {
    Route::patch('/products/{id}/stock', [ProductController::class, 'updateStock']);
});
