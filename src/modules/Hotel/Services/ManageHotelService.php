<?php

namespace Modules\Hotel\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Hotel\Models\Hotel;

class ManageHotelService
{
    /**
     * @throws ForbiddenException
     */
    public function list(User $user): Collection
    {
        $this->assertBaseAdmin();

        return $user->hotels()
            ->with('location')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function show(Hotel $hotel, User $user): Hotel
    {
        $this->assertBaseAdmin();
        $this->assertBelongsToAdmin($hotel, $user);

        $hotel->load(['location', 'terms']);

        return $hotel;
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function delete(Hotel $hotel, User $user): array
    {
        $this->assertBaseAdmin();
        $this->assertBelongsToAdmin($hotel, $user);

        $id = $hotel->id;
        $hotel->admin_base = null;
        $hotel->save();

        return [
            'code' => 'hotel_detached',
            'data' => [
                'id' => $id,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     */
    private function assertBaseAdmin(): void
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'hotels_access_denied',
                domain: 'hotel',
            );
        }
    }

    /**
     * @throws NotFoundException
     */
    private function assertBelongsToAdmin(Hotel $hotel, User $user): void
    {
        if ((int) $hotel->admin_base !== (int) $user->id) {
            throw new NotFoundException(
                errorCode: 'hotel_not_found',
                domain: 'hotel',
            );
        }
    }
}
