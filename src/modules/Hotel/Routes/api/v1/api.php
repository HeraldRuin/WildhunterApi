<?php

use Illuminate\Support\Facades\Route;
use Modules\Hotel\Controllers\HotelController;
use Modules\Hotel\Controllers\RoomAvailabilityController;

Route::post('/hotels/offers', [HotelController::class, 'getHotels']);
Route::post('/hotels/search', [HotelController::class, 'searchHotels']);
Route::get('/hotels/price-range', [HotelController::class, 'priceRange']);
Route::post('/hotels/rooms/check-availability', [HotelController::class, 'checkAvailability']);
Route::get('/hotels/{location}/{slug}', [HotelController::class, 'getHotel']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hotels/manage', [HotelController::class, 'manageList']);
    Route::get('/rooms', [RoomAvailabilityController::class, 'index']);
    Route::get('/rooms/availability', [RoomAvailabilityController::class, 'loadDates']);
});
