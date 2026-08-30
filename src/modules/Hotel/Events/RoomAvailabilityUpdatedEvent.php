<?php

namespace Modules\Hotel\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;

class RoomAvailabilityUpdatedEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    public const string ACTION_CREATED = 'created';
    public const string ACTION_CANCELLED = 'cancelled';
    public const string ACTION_STATUS_UPDATED = 'status_updated';

    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly int $hotelId;
    private readonly int $bookingId;
    private readonly string $startDate;
    private readonly string $endDate;
    private readonly string $status;
    private readonly string $statusName;
    /** @var list<int> */
    private readonly array $roomIds;

    /**
     * @param list<int> $roomIds
     */
    public function __construct(
        Booking $booking,
        private readonly string $action,
        array $roomIds = [],
    ) {
        $this->hotelId = (int) $booking->hotel_id;
        $this->bookingId = (int) $booking->id;
        $this->startDate = Carbon::parse($booking->start_date)->format('Y-m-d');
        $this->endDate = Carbon::parse($booking->end_date)->format('Y-m-d');
        $this->status = (string) $booking->status;
        $this->statusName = (string) $booking->statusName;
        $this->roomIds = array_values(array_map('intval', $roomIds));
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("hotel.{$this->hotelId}.room-availability"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'room.availability.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'hotel_id' => $this->hotelId,
            'booking_id' => $this->bookingId,
            'action' => $this->action,
            'status' => $this->status,
            'status_name' => $this->statusName,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'room_ids' => $this->roomIds,
        ];
    }

    /**
     * @param list<int> $roomIds
     */
    public static function dispatchSafely(Booking $booking, string $action, array $roomIds = []): void
    {
        if (empty($booking->hotel_id)) {
            return;
        }

        try {
            event(new self($booking, $action, $roomIds));
        } catch (\Throwable $e) {
            Log::warning('Room availability broadcast failed', [
                'hotel_id' => $booking->hotel_id,
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
