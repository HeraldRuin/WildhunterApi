<?php

use Illuminate\Support\Facades\Route;
use Modules\Hotel\Controllers\HotelController;
use Modules\Hotel\Controllers\ManageRoomController;
use Modules\Hotel\Controllers\RoomAvailabilityController;

Route::post('/hotels/offers', [HotelController::class, 'getHotels']);
Route::post('/hotels/search', [HotelController::class, 'searchHotels']);
Route::get('/hotels/price-range', [HotelController::class, 'priceRange']);
Route::post('/hotels/rooms/check-availability', [HotelController::class, 'checkAvailability']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hotels/manage', [HotelController::class, 'manageList']);
    Route::get('/hotels/manage/{hotel}', [HotelController::class, 'show'])->whereNumber('hotel');
    Route::put('/hotels/manage/{hotel}', [HotelController::class, 'update'])->whereNumber('hotel');
    Route::delete('/hotels/manage/{hotel}', [HotelController::class, 'destroy'])->whereNumber('hotel');
    Route::get('/rooms', [RoomAvailabilityController::class, 'index']);
    Route::get('/rooms/availability', [RoomAvailabilityController::class, 'loadDates']);
    Route::post('/rooms/{room}/publish', [ManageRoomController::class, 'publish']);
    Route::post('/rooms/{room}/hide', [ManageRoomController::class, 'hide']);
    Route::delete('/rooms/{room}', [ManageRoomController::class, 'destroy']);
});

Route::get('/hotels/{location}/{slug}', [HotelController::class, 'getHotel']);
