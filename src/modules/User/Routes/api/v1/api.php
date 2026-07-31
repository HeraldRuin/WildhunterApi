<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Controllers\AuthController;
use Modules\User\Controllers\PasswordController;
use Modules\User\Controllers\UserController;
use Modules\User\Controllers\UserWishListController;
use Modules\Weapon\Controllers\WeaponController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);

//Подписка на рассылку
Route::post('/user/newsletter/subscribe',[UserController::class, 'subscribe']);

//Сброс пароля
Route::post('/password/email', [PasswordController::class, 'sendResetCode']);
Route::post('/password/reset', [PasswordController::class, 'resetPassword']);

//Избранное
Route::post('/services/{hotel}/favorite', [UserWishListController::class, 'addFavorite']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'searchUsers']);
    Route::get('/user/current-password', [PasswordController::class, 'getCurrentPassword']);
    Route::post('/user/change-password', [PasswordController::class, 'updatePassword']);

    Route::get('/user/weapons', [WeaponController::class, 'index']);
    Route::post('/user/weapons', [WeaponController::class, 'store']);
    Route::put('/user/weapons/{id}', [WeaponController::class, 'update']);
    Route::delete('/user/weapons/{id}', [WeaponController::class, 'destroy']);

    Route::get('/user/avatars', [UserController::class, 'avatarHistory']);
    Route::get('/user/{user}', [UserController::class, 'searchUser'])->whereNumber('user');
    Route::post('/user', [UserController::class, 'profileUpdate']);

    //Избранное
    Route::post('/services/favorites', [UserWishListController::class, 'getFavorites']);
    Route::post('/services/{hotel}/favorites', [UserWishListController::class, 'checkFavorite']);
    Route::delete('/services/{hotel}/favorite', [UserWishListController::class, 'removeFavorite']);
 });
