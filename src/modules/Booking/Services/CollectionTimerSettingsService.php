<?php

namespace Modules\Booking\Services;

use App\Exceptions\ForbiddenException;
use App\Models\User;
use Modules\Booking\Dto\StoreCollectionTimerData;
use Modules\Hotel\Models\Hotel;

class CollectionTimerSettingsService
{
    public const int DEFAULT_TIMER_HOURS = 24;

    public const string TYPE_COLLECTION = 'collection';
    public const string TYPE_BEDS = 'beds';
    public const string TYPE_PREPAYMENT = 'prepayment';

    public const array TYPES = [
        self::TYPE_COLLECTION,
        self::TYPE_BEDS,
        self::TYPE_PREPAYMENT,
    ];

    private const array TYPE_COLUMNS = [
        self::TYPE_COLLECTION => 'collection_timer_hours',
        self::TYPE_BEDS => 'bed_timer_hours',
        self::TYPE_PREPAYMENT => 'paid_timer_hours',
    ];

    /**
     * @throws ForbiddenException
     */
    public function get(string $type, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $column = self::TYPE_COLUMNS[$type];

        return [
            'type' => $type,
            'timer_hours' => (int) ($hotel->{$column} ?? self::DEFAULT_TIMER_HOURS),
            'hotel_id' => $hotel->id,
        ];
    }

    /**
     * @throws ForbiddenException
     */
    public function save(StoreCollectionTimerData $data, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $column = self::TYPE_COLUMNS[$data->type];

        $hotel->{$column} = $data->timerHours;
        $hotel->save();

        return [
            'type' => $data->type,
            'timer_hours' => $data->timerHours,
            'hotel_id' => $hotel->id,
        ];
    }

    /**
     * @throws ForbiddenException
     */
    private function resolveHotel(User $user): Hotel
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'timer_settings_access_denied',
                domain: 'collection',
            );
        }

        $hotel = $user->hotels()->first();

        if (!$hotel) {
            throw new ForbiddenException(
                errorCode: 'hotel_required',
                domain: 'collection',
            );
        }

        return $hotel;
    }
}
