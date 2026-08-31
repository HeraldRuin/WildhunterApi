<?php

namespace Modules\Booking\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Services\BookingHistoryActionService;
use Modules\Hotel\Models\HotelRoomBooking;
use Modules\Role\Models\Role;

class BookingHistoryResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var Booking $booking */
        $booking = $this->resource;

        $invitations = $booking->getAllInvitations();
        $acceptedCount = $invitations->where('status', 'accepted')->count();
        $paidCount = $invitations->where('prepayment_paid', true)->count();
        $totalNeeded = $booking->getNeededHuntersCount();

        $calculation = $booking->calculation ?? [];
        $role = is_baseAdmin() ? Role::ADMIN : Role::CUSTOMER;
        $invitation = $booking->getCurrentUserInvitation();
        $mappedInvitations = $invitations->map(static function (BookingHunterInvitation $invitation) {
            $hunter = $invitation->hunter;
            $name = trim(implode(' ', array_filter([
                $hunter?->first_name,
                $hunter?->last_name,
            ])));

            return [
                'invitation_id' => $invitation->id,
                'hunter_id' => $invitation->hunter_id,
                'user_name' => $hunter?->user_name,
                'name' => $name ?: ($hunter?->user_name ?: $invitation->email),
                'email' => $hunter?->email ?: $invitation->email,
                'status' => __('statuses.invitation.' . $invitation->status),
                'is_accepted' => $invitation->status === BookingHunterInvitation::STATUS_ACCEPTED,
                'prepayment_paid' => (bool) $invitation->prepayment_paid,
                'prepayment_paid_status' => $invitation->prepayment_paid_status,
            ];
        })->values()->all();

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
            'status_for_user' => $booking->status_for_user,
            'status_label' => $booking->status_name_for_user,
            'display_status' => $booking->display_status ?? $booking->status,
            'is_paid' => (bool) $booking->is_paid,
            'is_master_hunter' => (bool) $booking->is_master_hunter,
            'is_invited' => (bool) $booking->is_invited,
            'invitation_accepted' => (bool) ($invitation && $invitation->status === BookingHunterInvitation::STATUS_ACCEPTED),

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

            'details' => $this->buildDetails($booking),

            'collection' => [
                'accepted_count' => $acceptedCount,
                'total_needed' => $totalNeeded,
                'paid_count' => $paidCount,
                'invitations' => $mappedInvitations,
                'collection_end_at' => $booking->getMeta('collection_end_at') ?: null,
                'paid_end_at' => $booking->getMeta('paid_end_at') ?: null,
                'beds_end_at' => $booking->getMeta('beds_end_at') ?: null,
            ],

            'payment' => [
                'prepaid_total' => (float) ($calculation['prepaid_total'] ?? 0),
                'base_total' => (float) ($calculation['base_total'] ?? 0),
                'total' => (float) ($calculation['total'] ?? 0),
            ],

            'available_actions' => app(BookingHistoryActionService::class)
                ->getAvailableActions($booking, $role),
        ];
    }

    private function buildDetails(Booking $booking): array
    {
        $rooms = $this->mapRooms($booking);

        return [
            'start_date' => $booking->start_date,
            'end_date' => $booking->end_date,
            'duration_days' => (int) $booking->duration_days,
            'total_guests' => (int) $booking->total_guests,
            'total_sleeping_places' => $this->resolveTotalSleepingPlaces($booking, $rooms),
            'amount_accommodation' => $this->resolveAccommodationTotal($booking),
            'amount_accommodation_per_person' => $this->resolveAccommodationPricePerPerson($booking),
            'start_date_animal' => $booking->start_date_animal,
            'total_hunting' => $booking->total_hunting,
            'amount_hunting' => $this->resolveHuntingTotal($booking),
            'amount_hunting_per_person' => $this->resolveHuntingPricePerPerson($booking),
            'animal' => $booking->animal ? [
                'id' => $booking->animal->id,
                'title' => $booking->animal->title,
                'price_total' => $this->resolveHuntingTotal($booking),
                'price_per_person' => $this->resolveHuntingPricePerPerson($booking),
                'price' => $this->resolveHuntingPricePerPerson($booking),
            ] : null,
            'rooms' => $rooms,
        ];
    }

    private function mapRooms(Booking $booking): array
    {
        $rooms = $booking->relationLoaded('roomsBooking')
            ? $booking->roomsBooking
            : $booking->roomsBooking()->with('room')->get();

        $totalGuests = (int) $booking->total_guests;

        return $rooms->map(function (HotelRoomBooking $roomBooking) use ($totalGuests) {
            $room = $roomBooking->room;
            $number = (int) $roomBooking->number;
            $placesPerRoom = $this->placesPerRoom($room);
            $priceTotal = (float) $roomBooking->price * $number;

            return [
                'room_id' => $roomBooking->room_id,
                'title' => $room?->title,
                'number' => $number,
                'price' => (float) $roomBooking->price,
                'price_total' => $priceTotal,
                'price_per_person' => $this->resolvePricePerPerson($priceTotal, $totalGuests),
                'adults' => (int) ($room?->adults ?? 0),
                'beds' => (int) ($room?->beds ?? 0),
                'places_per_room' => $placesPerRoom,
                'total_places' => $placesPerRoom * $number,
            ];
        })->values()->all();
    }

    /**
     * Вместимость одного экземпляра комнаты — число койко-мест.
     * Берём adults (как автораздача), иначе beds.
     */
    private function placesPerRoom(?object $room): int
    {
        if (!$room) {
            return 0;
        }

        $adults = (int) ($room->adults ?? 0);
        if ($adults > 0) {
            return $adults;
        }

        return max(0, (int) ($room->beds ?? 0));
    }

    private function resolveTotalSleepingPlaces(Booking $booking, array $rooms): ?int
    {
        if (!$this->hasAccommodation($booking)) {
            return null;
        }

        return (int) array_sum(array_column($rooms, 'total_places'));
    }

    private function hasAccommodation(Booking $booking): bool
    {
        return in_array($booking->type, [
            Booking::BookingTypeHotel,
            Booking::BookingTypeHotelAnimal,
        ], true);
    }

    private function resolveAccommodationTotal(Booking $booking): ?float
    {
        if (!$this->hasAccommodation($booking) || $booking->total === null) {
            return null;
        }

        return (float) $booking->total;
    }

    private function resolveAccommodationPricePerPerson(Booking $booking): ?float
    {
        if (!$this->hasAccommodation($booking) || $booking->total === null) {
            return null;
        }

        return $this->resolvePricePerPerson((float) $booking->total, (int) $booking->total_guests);
    }

    private function resolvePricePerPerson(float $total, int $personCount): ?float
    {
        if ($personCount <= 0) {
            return null;
        }

        return (float) round($total / $personCount, 2);
    }

    private function resolveHuntingTotal(Booking $booking): ?float
    {
        if ($booking->amount_hunting === null) {
            return null;
        }

        return (float) $booking->amount_hunting;
    }

    private function resolveHuntingPricePerPerson(Booking $booking): ?float
    {
        $amountHunting = $booking->amount_hunting;
        $totalHunting = $booking->total_hunting;

        if ($amountHunting === null || !$totalHunting) {
            return null;
        }

        return $this->resolvePricePerPerson((float) $amountHunting, (int) $totalHunting);
    }
}
