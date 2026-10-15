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

class BookingServicesUpdatedEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly int $bookingId;
    private readonly string $bookingCode;

    public function __construct(Booking $booking)
    {
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
        return 'booking.services.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'code' => $this->bookingCode,
        ];
    }

    public static function dispatchSafely(Booking $booking): void
    {
        try {
            event(new self($booking));
        } catch (\Throwable $e) {
            Log::warning('Booking broadcast failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
