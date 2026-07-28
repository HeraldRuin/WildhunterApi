<?php

use Illuminate\Support\Facades\Route;
use Modules\Weapon\Controllers\WeaponController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/weapons', [WeaponController::class, 'weapons']);
    Route::get('/calibers', [WeaponController::class, 'calibers']);
 });
