<?php

namespace Modules\User\Controllers;

use App\Models\User;
use App\Exceptions\ForbiddenException;
use Illuminate\Http\JsonResponse;
use Modules\User\Dto\SubscribeData;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\NotFoundException;
use Modules\User\Services\UserService;
use App\Http\Responses\SuccessResponse;
use Modules\User\Dto\ProfileUpdateData;
use Modules\User\Events\UserSubscriberSubmit;
use Modules\User\Http\Resources\AvatarHistoryResource;
use Modules\User\Http\Resources\HunterSearchResource;
use Modules\User\Http\Resources\UserSearchResource;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Http\Requests\SearchHuntersRequest;
use Modules\User\Http\Requests\SearchUsersRequest;
use Modules\User\Http\Requests\SubscribeRequest;
use Modules\User\Http\Requests\ProfileUpdateRequest;

class UserController
{
    public function __construct(protected UserService $userService)
    {
    }
    public function searchUsers(): JsonResponse
    {
        $result = $this->userService->searchAl();

        return new SuccessResponse(data: UserResource::collection($result));
    }

    /**
     * @throws ForbiddenException
     */
    public function searchCustomers(SearchUsersRequest $request): JsonResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $result = $this->userService->searchCustomers(
            trim($request->string('query')->toString()),
            $request->integer('booking_id'),
            $user,
        );

        return new SuccessResponse(data: UserSearchResource::collection($result));
    }

    /**
     * @throws ForbiddenException
     */
    public function searchHunters(SearchHuntersRequest $request): JsonResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $result = $this->userService->searchHunters(
            trim($request->string('query')->toString()),
            $request->integer('booking_id'),
            $user,
        );

        return new SuccessResponse(data: HunterSearchResource::collection($result));
    }

    /**
     * @throws NotFoundException
     */
    public function searchUser($id): JsonResponse
    {
        $result = $this->userService->searchById($id);

        return new SuccessResponse(data: new UserResource($result));
    }

    public function avatarHistory(): JsonResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $result = $this->userService->getAvatarHistory($user);

        return new SuccessResponse(data: AvatarHistoryResource::collection($result));
    }

    public function profileUpdate(ProfileUpdateRequest $request): JsonResponse
    {
        $dto = ProfileUpdateData::fromRequest($request);
        $result = $this->userService->update(Auth::user(), $dto);

        return new SuccessResponse(code: $result['code'], domain: 'user', data: new UserResource($result['user']));
    }

    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        $dto = SubscribeData::fromRequest($request);
        $result = $this->userService->subscribe($dto);

        if ($result['code'] !== 'subscription_already_subscribed') {
            event(new UserSubscriberSubmit($result['subscriber']));
        }

        return new SuccessResponse(code: $result['code'], domain: 'user');
    }
}
