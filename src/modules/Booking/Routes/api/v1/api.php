<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Controllers\BookingController;
use Modules\Booking\Controllers\CollectionTimerController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/settings/timers/{type}', [CollectionTimerController::class, 'show']);
    Route::put('/settings/timers/{type}', [CollectionTimerController::class, 'store']);

    Route::post('/bookings', [BookingController::class, 'store']);

    Route::get('/bookings/history', [BookingController::class, 'bookingHistory']);
    Route::post('/bookings/{code}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{code}/mark-paid', [BookingController::class, 'markPaid']);
    Route::post('/bookings/{code}/complete', [BookingController::class, 'complete']);
    Route::post('/bookings/{code}/start-collection', [BookingController::class, 'startCollection']);
    Route::post('/bookings/{code}/extend-collection', [BookingController::class, 'extendCollection']);
    Route::post('/bookings/{code}/finish-collection', [BookingController::class, 'finishCollection']);
    Route::post('/bookings/{code}/expire-prepayment', [BookingController::class, 'expirePrepaymentCollection']);
    Route::post('/bookings/{code}/prepayment-paid', [BookingController::class, 'storePrepayment']);
    Route::get('/bookings/{code}/payment-status', [BookingController::class, 'paymentStatus']);
    Route::post('/bookings/{code}/cancel-collection', [BookingController::class, 'cancelCollection']);
    Route::post('/bookings/{code}/invite-hunter', [BookingController::class, 'inviteHunter']);
    Route::delete('/bookings/{code}/remove-hunter', [BookingController::class, 'removeHunter']);
    Route::post('/bookings/{code}/replace-hunter', [BookingController::class, 'replaceHunter']);
    Route::post('/bookings/{code}/accept-invitation', [BookingController::class, 'acceptInvitation']);
    Route::post('/bookings/{code}/decline-invitation', [BookingController::class, 'declineInvitation']);
    Route::post('/bookings/{code}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{code}/change-user', [BookingController::class, 'changeCustomer']);

    Route::get('/bookings/{code}/checkout', [BookingController::class, 'checkout']);
    Route::post('/bookings/{code}/checkout', [BookingController::class, 'doCheckout']);
    Route::put('/bookings/customer-notes', [BookingController::class, 'updateCustomerNotes']);

    Route::get('/bookings/{code}/places', [BookingController::class, 'places']);
    Route::post('/bookings/{code}/select-place', [BookingController::class, 'selectPlace']);
    Route::post('/bookings/{code}/cancel-select-place', [BookingController::class, 'cancelSelectPlace']);

    Route::get('/bookings/{code}/calculating', [BookingController::class, 'calculating']);
    Route::get('/bookings/{code}/services', [BookingController::class, 'services']);
    Route::post('/bookings/{code}/services/trophies', [BookingController::class, 'storeTrophy']);
    Route::post('/bookings/{code}/services/penalties', [BookingController::class, 'storePenalty']);
    Route::post('/bookings/{code}/services/preparations', [BookingController::class, 'storePreparation']);
    Route::post('/bookings/{code}/services/foods', [BookingController::class, 'storeFood']);
    Route::post('/bookings/{code}/services/additionals', [BookingController::class, 'storeAdditional']);
    Route::post('/bookings/{code}/services/spendings', [BookingController::class, 'storeSpending']);
    Route::delete('/bookings/{code}/services/{serviceId}', [BookingController::class, 'deleteService']);
});
