<?php

namespace Modules\Booking\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Booking\Dto\BookingHistoryItemData;

class BookingHistoryResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var BookingHistoryItemData $item */
        $item = $this->resource;
        $booking = $item->booking;

        return [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'code' => $booking->code,
            'invitation_url' => '/profile/bookings?' . http_build_query([
                'status' => 'invitation',
                'code' => $booking->code,
            ]),
            'created_at' => $booking->created_at,
            'type' => $booking->type,
            'type_text' => $booking->type_text,
            'status' => $booking->status,
            'status_for_user' => $item->statusForUser,
            'status_label' => $item->statusLabel,
            'display_status' => $item->displayStatus,
            'is_paid' => (bool) $booking->is_paid,
            'is_master_hunter' => $item->isMasterHunter,
            'is_invited' => $item->isInvited,
            'invitation_accepted' => $item->invitationAccepted,

            'hotel' => $booking->hotel ? [
                'id' => $booking->hotel->id,
                'title' => $booking->hotel->title,
                'slug' => $booking->hotel->slug,
                'location' => $booking->hotel->location ? [
                    'slug' => $booking->hotel->location->slug,
                ] : null,
                'collection_timer_hours' => $booking->hotel->collection_timer_hours ?? null,
                'paid_timer_hours' => $booking->hotel->paid_timer_hours ?? null,
                'bed_timer_hours' => $booking->hotel->bed_timer_hours ?? null,
            ] : null,

            'creator' => $booking->creator ? [
                'id' => $booking->creator->id,
                'user_name' => $booking->creator->user_name,
                'first_name' => $booking->creator->first_name,
                'last_name' => $booking->creator->last_name,
                'email' => $booking->creator->email,
                'phone' => $booking->creator->phone,
            ] : null,

            'details' => $item->details,
            'collection' => $item->collection,
            'payment' => $item->payment,
            'available_actions' => $item->availableActions,
        ];
    }
}
