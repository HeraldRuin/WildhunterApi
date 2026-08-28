<?php

namespace Modules\Hotel\Services;

use App\Exceptions\ForbiddenException;
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
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'hotels_access_denied',
                domain: 'hotel',
            );
        }

        return $user->hotels()
            ->with('location')
            ->orderByDesc('updated_at')
            ->get();
    }
}
