<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Modules\Booking\Models\Booking;

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
