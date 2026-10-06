<?php

namespace Modules\Booking\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Animals\Http\Resources\AnimalResource;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Http\Resources\HotelShortResource;
use Modules\Hotel\Models\HotelRoomBooking;
use Modules\Location\Http\Resources\LocationResource;

class BookingCheckoutResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var Booking $booking */
        $booking = $this->resource;

        $booking->loadMissing(['hotel', 'animal']);

        $roomBookings = HotelRoomBooking::getByBookingId($booking->id)->load('room');
        $hasHotel = in_array($booking->type, [
            Booking::BookingTypeHotel,
            Booking::BookingTypeHotelAnimal,
        ], true);
        $hasAnimal = in_array($booking->type, [
            Booking::BookingTypeAnimal,
            Booking::BookingTypeHotelAnimal,
        ], true);

        $totalGuests = (int) $booking->total_guests;
        $amountHunting = $booking->amount_hunting !== null ? (float) $booking->amount_hunting : null;
        $amountHuntingPerPerson = $this->resolveHuntingPricePerPerson($booking);
        $amountAccommodationPerPerson = $this->resolveAccommodationPricePerPerson($booking);

        $animal = null;
        if ($hasAnimal && $booking->animal) {
            $animal = AnimalResource::make($booking->animal)->resolve();
            $animal['price_total'] = $amountHunting;
            $animal['price_per_person'] = $amountHuntingPerPerson;
        }

        return [
            'booking_number' => $booking->booking_number,
            'created_at' => $booking->created_at,
            'status' => $booking->status,
            'gateway' => booking_gateway_to_text($booking->gateway),
            'type' => $booking->type,
            'check_in' => $booking->start_date,
            'check_out' => $booking->end_date,
            'start_date_animal' => $booking->start_date_animal,

            'location' => LocationResource::make($booking->hotel->location),
            'hotel' => $hasHotel && $booking->hotel ? HotelShortResource::make($booking->hotel) : null,
            'animal' => $animal,

            'total' => (float) $booking->total,
            'amount_accommodation_per_person' => $amountAccommodationPerPerson,
            'amount_hunting' => (float) $booking->amount_hunting,
            'amount_hunting_per_person' => $amountHuntingPerPerson,
            'all_total' => (float) $booking->total + (float) $booking->amount_hunting,
            'deposit' => (float) ($booking->deposit ?? 0),
            'total_guests' => $totalGuests,
            'total_hunting' => $booking->total_hunting,
            'customer_notes' => $booking->customer_notes,

            'rooms' => $roomBookings->map(function (HotelRoomBooking $roomBooking) use ($totalGuests) {
                $number = (int) $roomBooking->number;
                $price = (float) $roomBooking->price;
                $priceTotal = $price * $number;

                return [
                    'room_id' => $roomBooking->room_id,
                    'title' => $roomBooking->room?->title,
                    'number' => $number,
                    'price' => $price,
                    'price_total' => $priceTotal,
                    'price_per_person' => $this->resolvePricePerPerson($priceTotal, $totalGuests),
                ];
            })->values()->all(),
        ];
    }

    private function resolveAccommodationPricePerPerson(Booking $booking): ?float
    {
        $hasAccommodation = in_array($booking->type, [
            Booking::BookingTypeHotel,
            Booking::BookingTypeHotelAnimal,
        ], true);

        if (!$hasAccommodation || $booking->total === null) {
            return null;
        }

        return $this->resolvePricePerPerson((float) $booking->total, (int) $booking->total_guests);
    }

    private function resolveHuntingPricePerPerson(Booking $booking): ?float
    {
        if ($booking->amount_hunting === null || !$booking->total_hunting) {
            return null;
        }

        return $this->resolvePricePerPerson((float) $booking->amount_hunting, (int) $booking->total_hunting);
    }

    private function resolvePricePerPerson(float $total, int $personCount): ?float
    {
        if ($personCount <= 0) {
            return null;
        }

        return (float) round($total / $personCount, 2);
    }
}
