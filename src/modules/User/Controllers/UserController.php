<?php

namespace Modules\User\Controllers;

use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\User\Dto\ProfileUpdateData;
use Modules\User\Dto\SubscribeData;
use Modules\User\Events\UserSubscriberSubmit;
use Modules\User\Http\Requests\ProfileUpdateRequest;
use Modules\User\Http\Requests\SubscribeRequest;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Http\Resources\UserWithWeaponResource;
use Modules\User\Services\UserService;

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
    public function searchUser($id): JsonResponse
    {
        $result = $this->userService->searchById($id);

        return new SuccessResponse(data: new UserWithWeaponResource($result));
    }

    public function profileUpdate(ProfileUpdateRequest $request): JsonResponse
    {
        $dto = ProfileUpdateData::fromRequest($request);
        $result = $this->userService->update(Auth::user(), $dto);

        return new SuccessResponse(code: $result['code'], domain: 'user', data: new UserWithWeaponResource($result['user']));
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
