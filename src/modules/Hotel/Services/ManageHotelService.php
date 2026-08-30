<?php

namespace Modules\Hotel\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Hotel\Dto\UpdateHotelManageData;
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
     */
    public function store(UpdateHotelManageData $data, User $user): array
    {
        $this->assertBaseAdmin();

        $hotel = DB::transaction(function () use ($data, $user) {
            $hotel = new Hotel();

            foreach ($data->fields as $field => $value) {
                $hotel->{$field} = $value;
            }

            if ($data->hasGallery) {
                $hotel->gallery = $data->galleryIds === [] || $data->galleryIds === null
                    ? null
                    : implode(',', $data->galleryIds);
            }

            $title = (string) ($hotel->title ?? '');
            $slug = $hotel->slug ?? null;
            $hotel->slug = $this->uniqueSlug($title, is_string($slug) ? $slug : null);

            if (empty($hotel->status)) {
                $hotel->status = 'draft';
            }

            $hotel->admin_base = $user->id;
            $hotel->author_id = $user->id;
            $hotel->create_user = $user->id;
            $hotel->save();

            if ($data->hasTermIds) {
                $hotel->terms()->sync($data->termIds ?? []);
            }

            return $hotel;
        });

        $hotel->load(['location', 'terms']);

        return [
            'code' => 'hotel_created',
            'data' => $hotel,
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function update(Hotel $hotel, UpdateHotelManageData $data, User $user): array
    {
        $this->assertBaseAdmin();
        $this->assertBelongsToAdmin($hotel, $user);

        DB::transaction(function () use ($hotel, $data) {
            foreach ($data->fields as $field => $value) {
                $hotel->{$field} = $value;
            }

            if ($data->hasGallery) {
                $hotel->gallery = $data->galleryIds === [] || $data->galleryIds === null
                    ? null
                    : implode(',', $data->galleryIds);
            }

            $hotel->save();

            if ($data->hasTermIds) {
                $hotel->terms()->sync($data->termIds ?? []);
            }
        });

        $hotel->load(['location', 'terms']);

        return [
            'code' => 'hotel_updated',
            'data' => $hotel,
        ];
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

    private function uniqueSlug(string $title, ?string $slug = null): string
    {
        $base = $slug !== null && $slug !== ''
            ? Str::slug($slug)
            : Str::slug($title);

        if ($base === '') {
            $base = 'hotel';
        }

        $candidate = $base;
        $i = 1;

        while (Hotel::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $i;
            $i++;
        }

        return $candidate;
    }
}
