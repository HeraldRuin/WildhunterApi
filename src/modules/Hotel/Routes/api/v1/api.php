<?php

use Illuminate\Support\Facades\Route;
use Modules\Hotel\Controllers\HotelController;

Route::post('/hotels/offers', [HotelController::class, 'getHotels']);
Route::post('/hotels/search', [HotelController::class, 'searchHotels']);
Route::get('/hotels/price-range', [HotelController::class, 'priceRange']);

Route::middleware('auth:sanctum')->group(function () {

});
