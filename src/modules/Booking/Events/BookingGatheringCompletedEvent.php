<?php

namespace Modules\Booking\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;

class BookingGatheringCompletedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly int $bookingId;

    private readonly string $bookingCode;

    private readonly string $status;

    /**
     * @param  list<int>  $removedHunterIds
     * @param  list<int>  $removedInvitationIds
     */
    public function __construct(
        Booking $booking,
        private readonly array $removedHunterIds,
        private readonly array $removedInvitationIds,
    ) {
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
        return 'booking.gathering.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'code' => $this->bookingCode,
            'status' => $this->status,
            'status_label' => booking_status_to_text($this->status),
            'removed_hunter_ids' => $this->removedHunterIds,
            'removed_invitation_ids' => $this->removedInvitationIds,
        ];
    }

    /**
     * @param  list<int>  $removedHunterIds
     * @param  list<int>  $removedInvitationIds
     */
    public static function dispatchSafely(
        Booking $booking,
        array $removedHunterIds,
        array $removedInvitationIds,
    ): void {
        try {
            event(new self($booking, $removedHunterIds, $removedInvitationIds));
        } catch (\Throwable $e) {
            Log::warning('Booking broadcast failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
