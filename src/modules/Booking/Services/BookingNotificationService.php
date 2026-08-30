<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Core\Dto\NotificationPayloadData;
use Modules\Core\Services\NotificationService;

class BookingNotificationService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    public function sendNewBooking(Booking $booking): void
    {
        $number = $this->bookingNumber($booking);
        $link = $this->bookingLink($booking);

        $baseAdmin = $this->baseAdmin($booking);

        if ($baseAdmin) {
            $this->sendSafely(
                $baseAdmin,
                new NotificationPayloadData(
                    title: __('booking.notifications.new_booking_admin_title'),
                    message: __('booking.notifications.new_booking_admin_message', [
                        'number' => $number,
                    ]),
                    link: $link,
                    category: 'booking',
                    entityType: 'booking',
                    entityId: (int) $booking->id,
                    event: 'booking.created',
                ),
                forAdmin: true,
            );
        }

        $this->sendToCreator(
            $booking,
            title: __('booking.notifications.new_booking_customer_title'),
            message: __('booking.notifications.new_booking_customer_message', [
                'number' => $number,
            ]),
            event: 'booking.created',
        );
    }

    public function sendBookingConfirmed(Booking $booking): void
    {
        $number = $this->bookingNumber($booking);

        $this->sendToCreator(
            $booking,
            title: __('booking.notifications.booking_confirmed_title'),
            message: __('booking.notifications.booking_confirmed_message', [
                'number' => $number,
            ]),
            event: 'booking.confirmed',
        );
    }

    public function sendBookingCancelled(Booking $booking): void
    {
        $number = $this->bookingNumber($booking);
        $title = __('booking.notifications.booking_cancelled_title');
        $message = __('booking.notifications.booking_cancelled_message', [
            'number' => $number,
        ]);

        if (is_baseAdmin()) {
            $this->sendToCreator(
                $booking,
                title: $title,
                message: $message,
                event: 'booking.cancelled',
            );

            return;
        }

        $baseAdmin = $this->baseAdmin($booking);

        if ($baseAdmin) {
            $this->sendSafely(
                $baseAdmin,
                new NotificationPayloadData(
                    title: $title,
                    message: $message,
                    link: $this->bookingLink($booking),
                    category: 'booking',
                    entityType: 'booking',
                    entityId: (int) $booking->id,
                    event: 'booking.cancelled',
                ),
                forAdmin: true,
            );
        }

        $creator = $this->creator($booking);

        if ($creator && (!$baseAdmin || (int) $creator->id !== (int) $baseAdmin->id)) {
            $this->sendToCreator(
                $booking,
                title: $title,
                message: $message,
                event: 'booking.cancelled',
            );
        }
    }

    public function sendCollectionStarted(Booking $booking): void
    {
        $number = $this->bookingNumber($booking);
        $link = $this->bookingLink($booking);
        $title = __('booking.notifications.collection_started_title');
        $message = __('booking.notifications.collection_started_message', [
            'number' => $number,
        ]);
        $payload = new NotificationPayloadData(
            title: $title,
            message: $message,
            link: $link,
            category: 'booking',
            entityType: 'booking',
            entityId: (int) $booking->id,
            event: 'booking.collection_started',
        );

        $baseAdmin = $this->baseAdmin($booking);

        if ($baseAdmin) {
            $this->sendSafely($baseAdmin, $payload, forAdmin: true);
        }

        foreach ($this->invitedHunters($booking) as $hunter) {
            if ($baseAdmin && (int) $hunter->id === (int) $baseAdmin->id) {
                continue;
            }

            $this->sendSafely($hunter, $payload, forAdmin: false);
        }
    }

    private function baseAdmin(Booking $booking): ?User
    {
        $booking->loadMissing('hotel');

        if (!$booking->hotel?->admin_base) {
            return null;
        }

        return User::query()->find($booking->hotel->admin_base);
    }

    private function sendToCreator(
        Booking $booking,
        string $title,
        string $message,
        string $event,
    ): void {
        $creator = $this->creator($booking);

        if (!$creator) {
            return;
        }

        $this->sendSafely(
            $creator,
            new NotificationPayloadData(
                title: $title,
                message: $message,
                link: $this->bookingLink($booking),
                category: 'booking',
                entityType: 'booking',
                entityId: (int) $booking->id,
                event: $event,
            ),
            forAdmin: false,
        );
    }

    private function creator(Booking $booking): ?User
    {
        $booking->loadMissing('creator');

        return $booking->creator;
    }

    /**
     * @return list<User>
     */
    private function invitedHunters(Booking $booking): array
    {
        $hunters = [];
        $seen = [];

        $invitations = $booking->invitationsQuery()
            ->whereNotNull('hunter_id')
            ->where(function ($query): void {
                $query->whereNull('status')
                    ->orWhereNotIn('status', [
                        BookingHunterInvitation::STATUS_DECLINED,
                        'removed',
                    ]);
            })
            ->get();

        foreach ($invitations as $invitation) {
            $hunter = $invitation->hunter;

            if (!$hunter || isset($seen[$hunter->id])) {
                continue;
            }

            $seen[$hunter->id] = true;
            $hunters[] = $hunter;
        }

        return $hunters;
    }

    private function bookingNumber(Booking $booking): string
    {
        return (string) ($booking->booking_number ?: $booking->code);
    }

    private function bookingLink(Booking $booking): string
    {
        return '/profile/bookings?booking_id=' . (int) $booking->id;
    }

    private function sendSafely(User $user, NotificationPayloadData $payload, bool $forAdmin): void
    {
        try {
            $this->notificationService->sendToUser($user, $payload, $forAdmin);
        } catch (\Throwable $exception) {
            Log::warning('Notification send failed', [
                'user_id' => $user->id,
                'for_admin' => $forAdmin,
                'event' => $payload->event,
                'entity_id' => $payload->entityId,
                'trace_id' => request()->attributes->get('trace_id'),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
