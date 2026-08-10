<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\BookingHistoryRequest;

class BookingHistoryData
{
    public function __construct(
        public ?string $status,
        public ?int $bookingId,
        public ?string $code,
    ) {}

    public static function fromRequest(BookingHistoryRequest $request): self
    {
        $data = $request->validated();

        return new self(
            status: $data['status'] ?? null,
            bookingId: isset($data['booking_id']) ? (int) $data['booking_id'] : null,
            code: $data['code'] ?? null,
        );
    }
}
