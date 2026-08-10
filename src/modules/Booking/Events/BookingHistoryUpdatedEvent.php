<?php

namespace Modules\Booking\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class BookingHistoryUpdatedEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    public const string ACTION_ADDED = 'added';
    public const string ACTION_REMOVED = 'removed';

    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly int $bookingId;
    private readonly string $bookingCode;

    public function __construct(
        Booking $booking,
        private readonly int $userId,
        private readonly string $action,
    ) {
        $this->bookingId = $booking->id;
        $this->bookingCode = $booking->code;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("booking-history.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.history.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'code' => $this->bookingCode,
            'action' => $this->action,
        ];
    }
}
