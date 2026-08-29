<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Modules\Booking\Models\Booking;
use Modules\Role\Models\Role;

Broadcast::channel('bookings.{bookingId}', function (User $user, int $bookingId): bool {
    return Booking::query()
        ->whereKey($bookingId)
        ->where(function ($query) use ($user): void {
            $query
                ->where('customer_id', $user->id)
                ->orWhereHas('bookingHunters', function ($query) use ($user): void {
                    $query->where('invited_by', $user->id);
                })
                ->orWhereHas('bookingHunters.invitations', function ($query) use ($user): void {
                    $query->where('hunter_id', $user->id);
                });
        })
        ->exists();
}, ['guards' => ['sanctum']]);

Broadcast::channel('booking-history.{userId}', function (User $user, int $userId): bool {
    return (int) $user->id === $userId;
}, ['guards' => ['sanctum']]);

Broadcast::channel('notifications.{userId}', function (User $user, int $userId): bool {
    return (int) $user->id === $userId;
}, ['guards' => ['sanctum']]);

Broadcast::channel('hotel.{hotelId}.room-availability', function (User $user, int $hotelId): bool {
    if (!$user->hasRole(Role::ADMIN)) {
        return false;
    }

    return $user->hotels()->whereKey($hotelId)->exists();
}, ['guards' => ['sanctum']]);
