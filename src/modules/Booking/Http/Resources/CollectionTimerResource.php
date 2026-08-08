<?php

namespace Modules\Booking\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Booking\Models\Booking;

class CollectionTimerResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var array{booking: Booking, start_at: string, end_at: string, hours: int} $result */
        $result = $this->resource;
        $booking = $result['booking'];

        return [
            'id' => $booking->id,
            'code' => $booking->code,
            'status' => $booking->status,
            'collection_start_at' => $result['start_at'],
            'collection_end_at' => $result['end_at'],
            'collection_timer_hours' => $result['hours'],
        ];
    }
}
