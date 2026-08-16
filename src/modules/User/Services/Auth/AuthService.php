<?php

namespace Modules\User\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Modules\User\Dto\Auth\LoginData;
use Illuminate\Support\Facades\Crypt;
use Modules\User\Services\UserService;
use App\Exceptions\ForbiddenException;
use Modules\User\Dto\Auth\RegisterData;
use App\Exceptions\UnauthorizedException;

class AuthService
{
    public function __construct(private UserService $userService)
    {
    }

    /**
     * @throws UnauthorizedException
     */
    public function login(LoginData $dto): array
    {
        $user = $this->userService->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw new UnauthorizedException(
                errorCode: 'auth_invalid_credentials',
                domain: 'auth'
            );
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @throws ForbiddenException
     */
    public function register(RegisterData $dto): array
    {
        if(!is_enable_registration()){
            throw new ForbiddenException(
               errorCode: 'registration_disabled',
               domain: 'auth'
            );
        }

        $user = User::create([
            'first_name' => $dto->first_name,
            'last_name'  => $dto->last_name,
            'user_name' => Str::slug("{$dto->first_name}_{$dto->last_name}", '_'),
            'email'      => $dto->email,
            'password'   => Hash::make($dto->password),
            'current_password' => Crypt::encryptString($dto->password),
            'status'    => 'publish',
            'phone'    => $dto->phone,
            'locale'   => app()->getLocale() ?: 'ru',
        ]);

        $user->assignRole($dto->role);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function refresh()
    {

    }
}
