<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Controllers\ReviewController;

Route::post('/services/{review}/reviews', [ReviewController::class, 'indexByService']);
Route::post('/services/reviews', [ReviewController::class, 'serviceReviews']);
Route::post('/review', [ReviewController::class, 'store']);
Route::get('reviews/ratings', [ReviewController::class, 'ratings']);
