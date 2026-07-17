<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Controllers\BookingController;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/users/{id}/bookings', [BookingController::class, 'bookingHistory']);
});
