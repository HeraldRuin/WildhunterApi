<?php

namespace Modules\Booking\Dto;

class CreateBookingRoomData
{
    public function __construct(
        public int $roomId,
        public int $number,
    ) {}
}
