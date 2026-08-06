<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Controllers\BookingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);

    Route::get('/bookings/history', [BookingController::class, 'bookingHistory']);

    Route::get('/bookings/{code}/checkout', [BookingController::class, 'checkout']);
    Route::post('/bookings/{code}/checkout', [BookingController::class, 'doCheckout']);
});
