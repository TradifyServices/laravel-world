<?php

use App\Http\Controllers\api\ImageController;
use App\Http\Controllers\CurrencyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('process-image',[ImageController::class,'processImage']);
Route::post('currency-exchange',[CurrencyController::class,'currencyExchange']);
