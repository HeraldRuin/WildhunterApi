<?php

namespace Modules\Booking\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Events\RoomAvailabilityUpdatedEvent;
use Modules\Hotel\Models\HotelRoomBooking;

class BookingUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly int $bookingId;
    private readonly string $bookingCode;
    private readonly string $status;

    public function __construct(Booking $booking)
    {
        $this->bookingId = $booking->id;
        $this->bookingCode = $booking->code;
        $this->status = $booking->status;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bookings.{$this->bookingId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'code' => $this->bookingCode,
            'status' => $this->status,
            'status_label' => booking_status_to_text($this->status),
        ];
    }

    /**
     * @param string|null $roomAvailabilityAction null — не трогать календарь номеров
     */
    public static function dispatchSafely(
        Booking $booking,
        ?string $roomAvailabilityAction = RoomAvailabilityUpdatedEvent::ACTION_STATUS_UPDATED,
    ): void {
        try {
            event(new self($booking));
        } catch (\Throwable $e) {
            Log::warning('Booking broadcast failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($roomAvailabilityAction === null) {
            return;
        }

        $roomIds = HotelRoomBooking::query()
            ->where('booking_id', $booking->id)
            ->pluck('room_id')
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($roomIds === []) {
            return;
        }

        RoomAvailabilityUpdatedEvent::dispatchSafely($booking, $roomAvailabilityAction, $roomIds);
    }
}
