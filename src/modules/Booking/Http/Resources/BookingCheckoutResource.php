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
            'animal' => $hasAnimal && $booking->animal ? AnimalResource::make($booking->animal) : null,

            'total' => (float) $booking->total,
            'amount_hunting' => (float) $booking->amount_hunting,
            'all_total' => (float) $booking->total + (float) $booking->amount_hunting,
            'deposit' => (float) ($booking->deposit ?? 0),
            'total_guests' => (int) $booking->total_guests,
            'total_hunting' => $booking->total_hunting,

            'rooms' => $roomBookings->map(static fn (HotelRoomBooking $roomBooking) => [
                'room_id' => $roomBooking->room_id,
                'title' => $roomBooking->room?->title,
                'number' => (int) $roomBooking->number,
                'price' => (float) $roomBooking->price,
            ])->values()->all(),
        ];
    }
}
