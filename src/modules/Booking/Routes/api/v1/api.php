<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Controllers\BookingController;

Route::post('/bookings', [BookingController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{id}/bookings', [BookingController::class, 'bookingHistory']);
});
