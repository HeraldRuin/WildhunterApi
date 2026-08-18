<?php

use Illuminate\Support\Facades\Route;
use Modules\Email\Controllers\SupportController;

Route::post('/support', [SupportController::class, 'send']);

