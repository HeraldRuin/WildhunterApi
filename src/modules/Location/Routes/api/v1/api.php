<?php

use Illuminate\Support\Facades\Route;
use Modules\Location\Controllers\LocationController;

Route::post('/locations/offers', [LocationController::class, 'getBestLocations']);
Route::get('/locations', [LocationController::class, 'getLocations']);
Route::get('/locations/{id}/hotels', [LocationController::class, 'getLocationHotels']);

Route::middleware('auth:sanctum')->group(function () {


 });
