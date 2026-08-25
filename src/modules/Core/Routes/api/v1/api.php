<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\NotificationController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])
        ->whereUuid('notificationId');
    Route::get('/notifications', [NotificationController::class, 'index']);
});
