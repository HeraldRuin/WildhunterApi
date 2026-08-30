<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Controllers\MediaController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/media/store', [MediaController::class, 'store']);
});
