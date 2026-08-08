<?php

namespace Modules\Booking\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

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
}
