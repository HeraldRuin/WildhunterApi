<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Controllers\BookingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);

    Route::get('/bookings/history', [BookingController::class, 'bookingHistory']);
    Route::post('/bookings/{code}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{code}/start-collection', [BookingController::class, 'startCollection']);
    Route::post('/bookings/{code}/extend-collection', [BookingController::class, 'extendCollection']);
    Route::post('/bookings/{code}/cancel-collection', [BookingController::class, 'cancelCollection']);
    Route::post('/bookings/{code}/accept-invitation', [BookingController::class, 'acceptInvitation']);
    Route::post('/bookings/{code}/decline-invitation', [BookingController::class, 'declineInvitation']);
    Route::post('/bookings/{code}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{code}/change-user', [BookingController::class, 'changeCustomer']);

    Route::get('/bookings/{code}/checkout', [BookingController::class, 'checkout']);
    Route::post('/bookings/{code}/checkout', [BookingController::class, 'doCheckout']);
    Route::put('/bookings/customer-notes', [BookingController::class, 'updateCustomerNotes']);
});
