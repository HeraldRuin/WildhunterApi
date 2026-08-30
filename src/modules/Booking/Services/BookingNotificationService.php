<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
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
        $number = (string) ($booking->booking_number ?: $booking->code);
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

        $creator = $this->creator($booking);

        if ($creator) {
            $this->sendSafely(
                $creator,
                new NotificationPayloadData(
                    title: __('booking.notifications.new_booking_customer_title'),
                    message: __('booking.notifications.new_booking_customer_message', [
                        'number' => $number,
                    ]),
                    link: $link,
                    category: 'booking',
                    entityType: 'booking',
                    entityId: (int) $booking->id,
                    event: 'booking.created',
                ),
                forAdmin: false,
            );
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

    private function creator(Booking $booking): ?User
    {
        $booking->loadMissing('creator');

        return $booking->creator;
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
