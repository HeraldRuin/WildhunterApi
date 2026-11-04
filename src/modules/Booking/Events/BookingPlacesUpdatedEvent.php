<?php

namespace Modules\Booking\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;

class BookingPlacesUpdatedEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    public const string ACTION_SELECTED = 'selected';

    public const string ACTION_CANCELLED = 'cancelled';

    public const string ACTION_ASSIGNED = 'assigned';

    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly int $bookingId;

    private readonly string $bookingCode;

    public function __construct(
        Booking $booking,
        private readonly string $action,
    ) {
        $this->bookingId = $booking->id;
        $this->bookingCode = $booking->code;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bookings.{$this->bookingId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.places.updated';
    }

    public function broadcastWith(): array
    {
        $booking = Booking::query()->find($this->bookingId);

        return [
            'booking_id' => $this->bookingId,
            'code' => $this->bookingCode,
            'action' => $this->action,
            'places' => BookingRoomPlace::occupiedMap($this->bookingId),
            'is_all_places_assigned' => (bool) ($booking->is_all_places_assigned ?? false),
            'status' => $booking->status ?? null,
        ];
    }

    public static function dispatchSafely(Booking $booking, string $action): void
    {
        try {
            event(new self($booking, $action));
        } catch (\Throwable $e) {
            Log::warning('Booking broadcast failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
