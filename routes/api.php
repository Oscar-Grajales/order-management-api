<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('throttle:orders');
