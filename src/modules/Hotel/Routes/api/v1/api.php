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
    Route::post('/hotels/manage', [HotelController::class, 'store']);
    Route::get('/hotels/manage/{hotel}', [HotelController::class, 'show'])->whereNumber('hotel');
    Route::put('/hotels/manage/{hotel}', [HotelController::class, 'update'])->whereNumber('hotel');
    Route::delete('/hotels/manage/{hotel}', [HotelController::class, 'destroy'])->whereNumber('hotel');
    Route::get('/rooms', [RoomAvailabilityController::class, 'index']);
    Route::get('/rooms/availability', [RoomAvailabilityController::class, 'loadDates']);
    Route::post('/rooms', [ManageRoomController::class, 'store']);
    Route::get('/rooms/{room}', [ManageRoomController::class, 'show'])->whereNumber('room');
    Route::put('/rooms/{room}', [ManageRoomController::class, 'update'])->whereNumber('room');
    Route::post('/rooms/{room}/publish', [ManageRoomController::class, 'publish'])->whereNumber('room');
    Route::post('/rooms/{room}/hide', [ManageRoomController::class, 'hide'])->whereNumber('room');
    Route::delete('/rooms/{room}', [ManageRoomController::class, 'destroy'])->whereNumber('room');
});

Route::get('/hotels/{location}/{slug}', [HotelController::class, 'getHotel']);
