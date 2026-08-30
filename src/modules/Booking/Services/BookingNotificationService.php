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

    public function sendBookingCancelled(Booking $booking, User $actor): void
    {
        $number = $this->bookingNumber($booking);
        $payload = new NotificationPayloadData(
            title: __('booking.notifications.booking_cancelled_title'),
            message: __('booking.notifications.booking_cancelled_message', [
                'number' => $number,
            ]),
            link: $this->bookingLink($booking),
            category: 'booking',
            entityType: 'booking',
            entityId: (int) $booking->id,
            event: 'booking.cancelled',
        );

        $masterHunter = $this->masterHunterUser($booking);
        $hunters = $this->invitedHunters($booking);
        $baseAdmin = $this->baseAdmin($booking);

        if (is_baseAdmin()) {
            if ($masterHunter) {
                $this->sendSafely($masterHunter, $payload, forAdmin: false);
            }

            foreach ($hunters as $hunter) {
                if ($masterHunter && (int) $hunter->id === (int) $masterHunter->id) {
                    continue;
                }

                $this->sendSafely($hunter, $payload, forAdmin: false);
            }

            return;
        }

        if ($baseAdmin) {
            $this->sendSafely($baseAdmin, $payload, forAdmin: true);
        }

        $masterId = $masterHunter?->id ?? (int) $actor->id;

        foreach ($hunters as $hunter) {
            if ((int) $hunter->id === (int) $masterId) {
                continue;
            }

            if ($baseAdmin && (int) $hunter->id === (int) $baseAdmin->id) {
                continue;
            }

            $this->sendSafely($hunter, $payload, forAdmin: false);
        }
    }

    public function sendCollectionStarted(Booking $booking): void
    {
        $baseAdmin = $this->baseAdmin($booking);

        if (!$baseAdmin) {
            return;
        }

        $number = $this->bookingNumber($booking);

        $this->sendSafely(
            $baseAdmin,
            new NotificationPayloadData(
                title: __('booking.notifications.collection_started_title'),
                message: __('booking.notifications.collection_started_message', [
                    'number' => $number,
                ]),
                link: $this->bookingLink($booking),
                category: 'booking',
                entityType: 'booking',
                entityId: (int) $booking->id,
                event: 'booking.collection_started',
            ),
            forAdmin: true,
        );
    }

    public function sendCollectionFinished(Booking $booking): void
    {
        $number = $this->bookingNumber($booking);
        $link = $this->bookingLink($booking);
        $title = __('booking.notifications.collection_finished_title');
        $message = __('booking.notifications.collection_finished_message', [
            'number' => $number,
        ]);
        $payload = new NotificationPayloadData(
            title: $title,
            message: $message,
            link: $link,
            category: 'booking',
            entityType: 'booking',
            entityId: (int) $booking->id,
            event: 'booking.collection_finished',
        );

        $baseAdmin = $this->baseAdmin($booking);

        if ($baseAdmin) {
            $this->sendSafely($baseAdmin, $payload, forAdmin: true);
        }

        foreach ($this->acceptedHunters($booking) as $hunter) {
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

    private function masterHunterUser(Booking $booking): ?User
    {
        $masterId = $booking->master_hunter_id;

        if (!$masterId) {
            return $this->creator($booking);
        }

        return User::query()->find($masterId);
    }

    /**
     * @return list<User>
     */
    private function invitedHunters(Booking $booking): array
    {
        return $this->huntersFromInvitations(
            $booking,
            statuses: null,
            excludeStatuses: [
                BookingHunterInvitation::STATUS_DECLINED,
                'removed',
            ],
        );
    }

    /**
     * @return list<User>
     */
    private function acceptedHunters(Booking $booking): array
    {
        return $this->huntersFromInvitations(
            $booking,
            statuses: [BookingHunterInvitation::STATUS_ACCEPTED],
        );
    }

    /**
     * @param  list<string>|null  $statuses
     * @param  list<string>  $excludeStatuses
     * @return list<User>
     */
    private function huntersFromInvitations(
        Booking $booking,
        ?array $statuses = null,
        array $excludeStatuses = [],
    ): array {
        $hunters = [];
        $seen = [];

        $query = $booking->invitationsQuery()->whereNotNull('hunter_id');

        if ($statuses !== null) {
            $query->whereIn('status', $statuses);
        } elseif ($excludeStatuses !== []) {
            $query->where(function ($q) use ($excludeStatuses): void {
                $q->whereNull('status')
                    ->orWhereNotIn('status', $excludeStatuses);
            });
        }

        foreach ($query->get() as $invitation) {
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
