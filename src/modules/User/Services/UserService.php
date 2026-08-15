<?php

namespace Modules\User\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\User;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Models\Hotel;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Models\MediaFile;
use Modules\Media\Services\MediaUploadService;
use Modules\User\Dto\SubscribeData;
use Modules\User\Models\Subscriber;
use Modules\User\Dto\ProfileUpdateData;
use Illuminate\Database\Eloquent\Collection;
use Modules\Role\Models\Role;
use Modules\User\Models\UserWishList;
use Modules\User\Models\UserAvatarHistory;

class UserService
{
    public function __construct(
        protected MediaUploadService $mediaUploadService,
    ) {}

    public function searchAl(): Collection
    {
        return User::with(['role', 'weapons', 'weapons.type', 'weapons.caliber'])->get();
    }

    /**
     * @throws NotFoundException
     */
    public function searchById(string $id): User
    {
        $user = User::with(['role', 'weapons', 'weapons.type', 'weapons.caliber'])->find($id);

        if (!$user) {
            throw new NotFoundException(
                errorCode: 'user_not_found',
                domain: 'user'
            );
        }

        return $user;
    }
    public function findByEmail(string $email): ?User
    {
        return User::with(['role', 'weapons', 'weapons.type', 'weapons.caliber'])->firstWhere('email', $email);
    }
    public function searchByQuery(string $query): Collection
    {
        return  User::with(['role', 'weapons', 'weapons.type', 'weapons.caliber'])->where(function ($q) use ($query) {
        $q->where('user_name', 'LIKE', $query.'%')
            ->orWhere('first_name', 'LIKE', $query.'%')
            ->orWhere('last_name', 'LIKE', $query.'%')
            ->orWhere('email', 'LIKE', $query.'%')
            ->orWhere('id', 'LIKE', $query.'%');
    })
            ->select(['id', 'user_name', 'first_name', 'last_name'])
            ->get();
    }

    /**
     * @throws ForbiddenException
     */
    public function searchCustomers(string $query, int $bookingId, User $actor): Collection
    {
        if (!$actor->hasRole(Role::ADMIN)) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        $booking = Booking::query()
            ->whereKey($bookingId)
            ->whereIn('hotel_id', $actor->hotels()->select('id'))
            ->first();

        if (!$booking) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        $currentCustomerId = (int) ($booking->create_user ?: $booking->customer_id);

        return $this->searchByIdQuery($query, $currentCustomerId);
    }

    /**
     * @throws ForbiddenException
     */
    public function searchHunters(string $query, int $bookingId, User $actor): Collection
    {
        $canManageCollection = Booking::query()
            ->whereKey($bookingId)
            ->whereHas('masterHunter', function ($masterHunterQuery) use ($actor) {
                $masterHunterQuery->where('invited_by', $actor->id);
            })
            ->exists();

        if (!$canManageCollection) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        return User::query()
            ->whereHas('role', function ($roleQuery) {
                $roleQuery->where('code', Role::CUSTOMER);
            })
            ->whereKeyNot($actor->id)
            ->whereNotIn('id', function ($invitedHuntersQuery) use ($bookingId) {
                $invitedHuntersQuery
                    ->select('invitations.hunter_id')
                    ->from('bc_booking_hunter_invitations as invitations')
                    ->join(
                        'bc_booking_hunters as booking_hunters',
                        'booking_hunters.id',
                        '=',
                        'invitations.booking_hunter_id',
                    )
                    ->where('booking_hunters.booking_id', $bookingId)
                    ->whereNull('booking_hunters.deleted_at')
                    ->whereNull('invitations.deleted_at')
                    ->whereNotNull('invitations.hunter_id')
                    ->whereNotIn('invitations.status', ['declined', 'removed']);
            })
            ->where(function ($userQuery) use ($query) {
                $userQuery->where('id', 'like', "{$query}%")
                    ->orWhere('user_name', 'like', "{$query}%")
                    ->orWhere('first_name', 'like', "{$query}%")
                    ->orWhere('last_name', 'like', "{$query}%")
                    ->orWhere('email', 'like', "{$query}%");
            })
            ->limit(10)
            ->get([
                'id',
                'user_name',
                'first_name',
                'last_name',
                'email',
                'phone',
                'role_id',
            ]);
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function searchReplacementHunters(string $query, int $bookingId, User $actor): Collection
    {
        $booking = Booking::query()->find($bookingId);

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $canReplaceHunters = $booking->masterHunter()
            ->where('invited_by', $actor->id)
            ->exists();

        if (!$canReplaceHunters) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        return User::query()
            ->whereHas('role', function ($roleQuery) {
                $roleQuery->where('code', Role::CUSTOMER);
            })
            ->whereKeyNot($actor->id)
            ->whereNotIn('id', function ($bookingHuntersQuery) use ($bookingId) {
                $bookingHuntersQuery
                    ->select('invitations.hunter_id')
                    ->from('bc_booking_hunter_invitations as invitations')
                    ->join(
                        'bc_booking_hunters as booking_hunters',
                        'booking_hunters.id',
                        '=',
                        'invitations.booking_hunter_id',
                    )
                    ->where('booking_hunters.booking_id', $bookingId)
                    ->whereNull('booking_hunters.deleted_at')
                    ->whereNull('invitations.deleted_at')
                    ->whereNotNull('invitations.hunter_id');
            })
            ->where(function ($userQuery) use ($query) {
                $userQuery->where('id', 'like', "{$query}%")
                    ->orWhere('user_name', 'like', "{$query}%")
                    ->orWhere('first_name', 'like', "{$query}%")
                    ->orWhere('last_name', 'like', "{$query}%")
                    ->orWhere('email', 'like', "{$query}%");
            })
            ->limit(10)
            ->get([
                'id',
                'user_name',
                'first_name',
                'last_name',
                'email',
                'phone',
                'role_id',
            ]);
    }

    private function searchByIdQuery(string $query, int $excludedUserId): Collection
    {
        return User::query()
            ->whereHas('role', function ($roleQuery) {
                $roleQuery->where('code', Role::CUSTOMER);
            })
            ->whereKeyNot($excludedUserId)
            ->where('id', 'like', "%{$query}%")
            ->get(['id', 'user_name', 'first_name', 'last_name', 'email', 'phone']);
    }

    public function update($user, ProfileUpdateData $dto): array
    {
        $user->fill(array_filter([
            'first_name' => $dto->first_name,
            'last_name' => $dto->last_name,
            'user_name' => $dto->nik,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'city' => $dto->city,
            'address' => $dto->address,
            'birthday' => date("Y-m-d", strtotime($dto->birthday)),
            'hunter_billet_number' => $dto->hunter_billet_number,
        ], fn($v) => $v !== null));

        $user->bio = $dto->bio ? strip_tags($dto->bio) : null;
        $user->updateFullName();

        $previousAvatarId = $user->avatar_id ? (int) $user->avatar_id : null;

        if ($dto->avatar) {
            $media = $this->mediaUploadService->uploadAvatar($dto->avatar, $user->id);
            $user->avatar_id = $media->id;
            $this->rememberAvatarHistory($user->id, $media->id);
        } elseif ($dto->avatar_id) {
            $this->assertAvatarSelectable($user, $dto->avatar_id);
            $user->avatar_id = $dto->avatar_id;
            $this->rememberAvatarHistory($user->id, $dto->avatar_id);
        }

        $user->save();
        $user->refresh();

        if ($previousAvatarId && $previousAvatarId !== (int) $user->avatar_id) {
            FileHelper::forgetUrlCache($previousAvatarId);
        }

        if ($user->avatar_id) {
            FileHelper::forgetUrlCache((int) $user->avatar_id);
        }

        return [
            'code' => 'update_success',
            'user' => $user,
        ];
    }

    public function getAvatarHistory(User $user): Collection
    {
        if ($user->avatar_id) {
            $this->rememberAvatarHistory($user->id, (int) $user->avatar_id);
        }

        return MediaFile::query()
            ->join('user_avatar_history as history', 'history.media_id', '=', 'media_files.id')
            ->where('history.user_id', $user->id)
            ->where('media_files.file_type', 'like', 'image/%')
            ->orderByDesc('history.id')
            ->limit(8)
            ->select('media_files.*')
            ->get();
    }

    private function rememberAvatarHistory(int $userId, int $mediaId): void
    {
        UserAvatarHistory::query()->firstOrCreate([
            'user_id' => $userId,
            'media_id' => $mediaId,
        ]);
    }

    /**
     * @throws ForbiddenException
     */
    private function assertAvatarSelectable(User $user, int $mediaId): void
    {
        $isInHistory = UserAvatarHistory::query()
            ->where('user_id', $user->id)
            ->where('media_id', $mediaId)
            ->exists();

        if ($isInHistory) {
            return;
        }

        $isAvatarUpload = MediaFile::query()
            ->where('id', $mediaId)
            ->where('author_id', $user->id)
            ->where('folder_id', MediaUploadService::FOLDER_AVATAR)
            ->where('file_type', 'like', 'image/%')
            ->exists();

        if (!$isAvatarUpload) {
            throw new ForbiddenException(
                errorCode: 'avatar_not_allowed',
                domain: 'user',
            );
        }
    }

    public function subscribe(SubscribeData $dto): array
    {
        $code = 'subscription_success';

        $subscriber = Subscriber::withTrashed()
            ->where('email', $dto->email)
            ->first();

        if ($subscriber) {
            if ($subscriber->trashed()) {
                $subscriber->restore();
                $code = 'subscription_thanks';
            } else {
                $code = 'subscription_already_subscribed';
            }
        } else {
            $user = User::select('first_name', 'last_name')
                ->where('email', $dto->email)
                ->first();

            $subscriber = new Subscriber();
            $subscriber->email = $dto->email;

            if ($user) {
                $subscriber->first_name = $user?->first_name;
                $subscriber->last_name = $user?->last_name;
            }
            $subscriber->save();
        }

        return [
            'code' => $code,
            'subscriber' => $subscriber,
        ];
    }

    public function check(?User $user, ?Hotel $hotel, string $object_model): array
    {
        $result = UserWishList::where("object_model", $object_model)
           ->where("object_id", $hotel->id)
            ->where("user_id", $user->id)
            ->exists();

        return [
            'is_in_wishList' => $result,
        ];
    }

    public function getFavorites(?User $user, string $object_model): array
    {
        $wishList = UserWishList::where("object_model", $object_model)
            ->where("user_id", $user->id)
            ->get();

        return [
            'wishList' => $wishList,
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws ConflictException
     * @throws ValidationException
     */
    public function addFavorite(?User $user, ?Hotel $hotel, string $object_model): array
    {
        if (!$hotel) {
            throw new ValidationException(
                errorCode: 'hotel_not_found',
                domain: 'hotel'
            );
        }

        if (!$user) {
            throw new ForbiddenException(
                errorCode: 'register_for_more_features',
                domain: 'auth'
            );
        }

//        $allServices = get_bookable_services();
//        if (empty($allServices[$object_model])) {
////            return $this->sendError(__('Service type not found'));
//        }

        $wishList = UserWishList::where("object_id", $hotel->id)
            ->where("object_model", $object_model)
            ->where("user_id", $user->id)
            ->first();

        if ($wishList) {
            throw new ConflictException(
                errorCode: 'already_favorite',
                domain: 'wishlist'
            );
        }

        $wishList = UserWishList::create([
            'object_id' => $hotel->id,
            'object_model' => $object_model,
            'user_id' => $user->id,
            'create_user' => $user->id,
        ]);

        return [
            'code' => 'added_to_favorites',
            'wishList' => $wishList,
        ];
    }

    /**
     * @throws ConflictException
     */
    public function removeFavorite(?User $user, Hotel $hotel, string $object_model): array
    {
        $wishList = UserWishList::where("object_id", $hotel->id)
            ->where("object_model", $object_model)
            ->where("user_id", $user->id)
            ->exists();

        if (!$wishList) {
            throw new ConflictException(
                errorCode: 'already_deleted',
                domain: 'wishlist'
            );
        }

        UserWishList::where("object_id", $hotel->id)
            ->where("object_model", $object_model)
            ->where("user_id", $user->id)
            ->delete();

        $wishList = UserWishList::where("object_model", $object_model)
            ->where("user_id", $user->id)
            ->get();

        return [
            'code' => 'deleted_from_favorites',
            'wishList' => $wishList,
        ];
    }
}
