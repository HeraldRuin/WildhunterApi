<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Booking\Dto\BookingHistoryItemData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelRoomBooking;

class BookingHistoryItemPresenter
{
    public function __construct(
        private readonly BookingHistoryActionService $bookingHistoryActionService,
    ) {}

    /**
     * @param array<string, string> $timerMeta
     */
    public function present(
        Booking $booking,
        User $user,
        string $role,
        array $timerMeta = [],
    ): BookingHistoryItemData {
        $invitations = $this->resolveInvitations($booking);
        $currentInvitation = $this->resolveCurrentUserInvitation($booking, (int) $user->id);
        $isInvited = $this->isInvited($booking, (int) $user->id);
        $isMasterHunter = $this->isMasterHunter($booking, (int) $user->id);
        $statusForUser = $this->resolveStatusForUser($booking, (int) $user->id, $isInvited);
        $rooms = $this->mapRooms($booking);

        return new BookingHistoryItemData(
            booking: $booking,
            statusForUser: $statusForUser,
            statusLabel: booking_status_to_text($statusForUser),
            displayStatus: (string) ($booking->display_status ?? $booking->status),
            isMasterHunter: $isMasterHunter,
            isInvited: $isInvited,
            invitationAccepted: $currentInvitation?->status === BookingHunterInvitation::STATUS_ACCEPTED,
            collection: [
                'accepted_count' => $invitations
                    ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
                    ->count(),
                'total_needed' => $this->resolveNeededHuntersCount($booking),
                'paid_count' => $invitations->where('prepayment_paid', true)->count(),
                'invitations' => $this->mapInvitations($invitations),
                'collection_end_at' => ($timerMeta['collection_end_at'] ?? null) ?: null,
                'paid_end_at' => ($timerMeta['paid_end_at'] ?? null) ?: null,
                'beds_end_at' => ($timerMeta['beds_end_at'] ?? null) ?: null,
            ],
            details: $this->buildDetails($booking, $rooms),
            payment: $this->buildPayment($booking),
            availableActions: $this->bookingHistoryActionService->getAvailableActions($booking, $role),
        );
    }

    /**
     * @return Collection<int, BookingHunterInvitation>
     */
    private function resolveInvitations(Booking $booking): Collection
    {
        if ($booking->relationLoaded('hunterInvitations')) {
            return $booking->hunterInvitations
                ->sortByDesc(static fn (BookingHunterInvitation $invitation) => $invitation->invited_at)
                ->values();
        }

        return $booking->getAllInvitations();
    }

    private function resolveCurrentUserInvitation(Booking $booking, int $userId): ?BookingHunterInvitation
    {
        if ($booking->relationLoaded('hunterInvitations')) {
            return $booking->hunterInvitations->first(
                static fn (BookingHunterInvitation $invitation) => $invitation->hunter_id === $userId
                    && !in_array($invitation->status, ['declined', 'removed'], true),
            );
        }

        return $booking->getCurrentUserInvitation();
    }

    private function isInvited(Booking $booking, int $userId): bool
    {
        if ($booking->relationLoaded('bookingHunters')) {
            $isBookingHunter = $booking->bookingHunters
                ->contains(static fn ($hunter) => (int) $hunter->invited_by === $userId);

            if ($isBookingHunter) {
                return false;
            }
        }

        if ($booking->relationLoaded('hunterInvitations')) {
            return $booking->hunterInvitations->contains(
                static fn (BookingHunterInvitation $invitation) => (int) $invitation->hunter_id === $userId
                    && !in_array($invitation->status, ['declined', 'removed'], true),
            );
        }

        return $booking->isInvited($userId);
    }

    private function isMasterHunter(Booking $booking, int $userId): bool
    {
        if (!$booking->relationLoaded('bookingHunters')) {
            return (bool) $booking->is_master_hunter;
        }

        return $booking->bookingHunters->contains(
            static fn ($hunter) => (int) $hunter->invited_by === $userId && (bool) $hunter->is_master,
        );
    }

    private function resolveStatusForUser(Booking $booking, int $userId, bool $isInvited): string
    {
        $creatorId = (int) ($booking->create_user ?? $booking->customer_id);

        if ($userId === $creatorId) {
            return (string) $booking->status;
        }

        if ($isInvited) {
            return match ($booking->status) {
                Booking::CANCELLED,
                Booking::PROCESSING,
                Booking::CONFIRMED,
                Booking::FINISHED_COLLECTION,
                Booking::PREPAYMENT_COLLECTION,
                Booking::FINISHED_PREPAYMENT,
                Booking::BED_COLLECTION,
                Booking::FINISHED_BED,
                Booking::PAID,
                Booking::COMPLETED => (string) $booking->status,
                default => Booking::START_COLLECTION,
            };
        }

        return (string) $booking->status;
    }

    private function resolveNeededHuntersCount(Booking $booking): int
    {
        $count = match ($booking->type) {
            Booking::BookingTypeHotel => (int) ($booking->total_guests ?? 0),
            Booking::BookingTypeAnimal,
            Booking::BookingTypeHotelAnimal => (int) ($booking->total_hunting ?? 0),
            default => 0,
        };

        return max(0, $count);
    }

    /**
     * @param Collection<int, BookingHunterInvitation> $invitations
     * @return list<array<string, mixed>>
     */
    private function mapInvitations(Collection $invitations): array
    {
        return $invitations->map(static function (BookingHunterInvitation $invitation) {
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
    }

    /**
     * @param list<array<string, mixed>> $rooms
     * @return array<string, mixed>
     */
    private function buildDetails(Booking $booking, array $rooms): array
    {
        $amountHunting = $this->resolveHuntingTotal($booking);
        $amountHuntingPerPerson = $this->resolveHuntingPricePerPerson($booking);

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
            'amount_hunting' => $amountHunting,
            'amount_hunting_per_person' => $amountHuntingPerPerson,
            'animal' => $booking->animal ? [
                'id' => $booking->animal->id,
                'title' => $booking->animal->title,
                'price_total' => $amountHunting,
                'price_per_person' => $amountHuntingPerPerson,
                'price' => $amountHuntingPerPerson,
            ] : null,
            'rooms' => $rooms,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
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

    private function placesPerRoom(?HotelRoom $room): int
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

    /**
     * @param list<array<string, mixed>> $rooms
     */
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

    /**
     * @return array<string, float>
     */
    private function buildPayment(Booking $booking): array
    {
        $calculation = $booking->calculation ?? [];

        return [
            'prepaid_total' => (float) ($calculation['prepaid_total'] ?? 0),
            'base_total' => (float) ($calculation['base_total'] ?? 0),
            'total' => (float) ($calculation['total'] ?? 0),
        ];
    }
}
