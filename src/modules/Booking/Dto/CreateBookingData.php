<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\BookingCreateRequest;

class CreateBookingData
{
    /**
     * @param CreateBookingRoomData[] $rooms
     */
    public function __construct(
        public int $hotelId,
        public string $checkIn,
        public string $checkOut,
        public int $adults,
        public int $hunters,
        public ?int $animalId,
        public array $rooms,
    ) {}

    public static function fromRequest(BookingCreateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            hotelId: (int) $data['hotel_id'],
            checkIn: $data['check_in'],
            checkOut: $data['check_out'],
            adults: (int) ($data['adults'] ?? 1),
            hunters: (int) ($data['hunters'] ?? 0),
            animalId: isset($data['animal_id']) ? (int) $data['animal_id'] : null,
            rooms: array_map(
                static fn (array $room) => new CreateBookingRoomData(
                    roomId: (int) $room['room_id'],
                    number: (int) $room['number'],
                ),
                $data['rooms']
            ),
        );
    }
}
