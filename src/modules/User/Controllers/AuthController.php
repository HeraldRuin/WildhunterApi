<?php

namespace Modules\User\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Http\Responses\AuthSuccessResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Dto\Auth\LoginData;
use Modules\User\Dto\Auth\RegisterData;
use Modules\User\Http\Requests\Auth\LoginRequest;
//use Modules\User\Http\Requests\Auth\RefreshTokenRequest;
use Modules\User\Http\Requests\Auth\RegisterRequest;
use Modules\User\Services\Auth\AuthService;

class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * @throws UnauthorizedException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginData::fromRequest($request);
        $result = $this->authService->login($dto);

        return new AuthSuccessResponse($result['token'], $result['user']);
    }

    /**
     * @throws UnauthorizedException
     */
    public function logout(Request $request): Response
    {
        if (!$request->user()) {
            throw new UnauthorizedException(
                errorCode: 'auth_user_false',
                domain: 'auth'
            );
        }

        $request->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }

    /**
     * @throws ForbiddenException
     */
    public function register(RegisterRequest $request): JsonResponse
        {
            $dto = RegisterData::fromRequest($request);
            $result = $this->authService->register($dto);

            event(new Registered($result['user']));

            return new AuthSuccessResponse(
                $result['token'],
                $result['user'],
                code: 'register_successfully',
                domain: 'auth'
            );
        }

//    public function refreshToken(RefreshTokenRequest $request)
//    {
//
//    }
}
