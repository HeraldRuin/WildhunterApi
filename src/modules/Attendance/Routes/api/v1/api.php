<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Controllers\AdditionalController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/services/additionals', [AdditionalController::class, 'index']);
    Route::get('/services/system', [AdditionalController::class, 'systemIndex']);
    Route::post('/services/additionals', [AdditionalController::class, 'store']);
    Route::put('/services/additionals/{additional}', [AdditionalController::class, 'update']);
    Route::delete('/services/additionals/{additional}', [AdditionalController::class, 'destroy']);
});
