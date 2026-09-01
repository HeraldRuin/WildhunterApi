<?php

namespace Modules\Attendance\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Attendance\Dto\StoreAdditionalData;
use Modules\Attendance\Dto\UpdateAdditionalData;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Hotel\Models\Hotel;

class AdditionalService
{
    /**
     * @throws ForbiddenException
     */
    public function list(User $user): Collection
    {
        $hotel = $this->resolveHotel($user);

        return AddetionalPrice::query()
            ->forHotel($hotel->id)
            ->orderByRaw("name = ? DESC", [AddetionalPrice::FOOD_NAME])
            ->orderBy('id')
            ->get();
    }

    /**
     * @throws ForbiddenException
     */
    public function store(StoreAdditionalData $data, User $user): array
    {
        $hotel = $this->resolveHotel($user);

        $additional = AddetionalPrice::create([
            'name' => $data->name,
            'price' => $data->price,
            'hotel_id' => $hotel->id,
            'user_id' => $user->id,
            'is_system' => $data->isSystem,
        ]);

        return [
            'code' => 'additional_saved',
            'data' => [
                'additional' => $additional,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function update(AddetionalPrice $additional, UpdateAdditionalData $data, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertBelongsToHotel($additional, $hotel);

        $payload = [
            'price' => $data->price,
        ];

        if (!$additional->isFood()) {
            $payload['name'] = $data->name;
            $payload['count'] = $data->count;
            $payload['calculation_type'] = $data->calculationType;
        }

        $additional->update($payload);

        return [
            'code' => 'additional_updated',
            'data' => [
                'additional' => $additional->fresh(),
            ],
        ];
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function delete(AddetionalPrice $additional, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertBelongsToHotel($additional, $hotel);

        if ($additional->isFood()) {
            throw new ConflictException(
                errorCode: 'additional_cannot_delete',
                domain: 'additional',
            );
        }

        $id = $additional->id;
        $additional->delete();

        return [
            'code' => 'additional_deleted',
            'data' => [
                'id' => $id,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     */
    private function resolveHotel(User $user): Hotel
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'services_access_denied',
                domain: 'additional',
            );
        }

        $hotel = $user->hotels()->first();

        if (!$hotel) {
            throw new ForbiddenException(
                errorCode: 'hotel_required',
                domain: 'additional',
            );
        }

        return $hotel;
    }

    /**
     * @throws NotFoundException
     */
    private function assertBelongsToHotel(AddetionalPrice $additional, Hotel $hotel): void
    {
        if ((int) $additional->hotel_id !== (int) $hotel->id) {
            throw new NotFoundException(
                errorCode: 'additional_not_found',
                domain: 'additional',
            );
        }
    }
}
