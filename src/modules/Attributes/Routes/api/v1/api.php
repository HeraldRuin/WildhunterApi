<?php

use Illuminate\Support\Facades\Route;
use Modules\Attributes\Controllers\AttributesController;

Route::post('/services/attributes', [AttributesController::class, 'getHotelAttributes']);

Route::middleware('auth:sanctum')->group(function () {

});
