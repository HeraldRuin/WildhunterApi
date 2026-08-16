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
use Modules\Booking\Models\BookingHunterInvitation;

class BookingInvitationUpdatedEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    public const string ACTION_ACCEPTED = 'accepted';
    public const string ACTION_DECLINED = 'declined';

    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly int $bookingId;
    private readonly string $bookingCode;
    private readonly int $invitationId;
    private readonly int $hunterId;
    private readonly string $status;
    private readonly string $statusLabel;

    public function __construct(
        Booking $booking,
        BookingHunterInvitation $invitation,
        private readonly string $action,
    ) {
        $this->bookingId = $booking->id;
        $this->bookingCode = $booking->code;
        $this->invitationId = $invitation->id;
        $this->hunterId = (int) $invitation->hunter_id;
        $this->status = $invitation->status;
        $this->statusLabel = __('statuses.invitation.' . $invitation->status);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bookings.{$this->bookingId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.invitation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'code' => $this->bookingCode,
            'invitation_id' => $this->invitationId,
            'hunter_id' => $this->hunterId,
            'action' => $this->action,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'is_accepted' => $this->status === BookingHunterInvitation::STATUS_ACCEPTED,
        ];
    }

    public static function dispatchSafely(
        Booking $booking,
        BookingHunterInvitation $invitation,
        string $action,
    ): void {
        try {
            event(new self($booking, $invitation, $action));
        } catch (\Throwable $e) {
            Log::warning('Booking broadcast failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
