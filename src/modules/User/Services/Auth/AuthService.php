<?php

namespace Modules\User\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Modules\Hotel\Models\Hotel;
use Modules\User\Dto\Auth\LoginData;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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

        $user = DB::transaction(function () use ($dto) {
            $user = User::create([
                'first_name' => $dto->first_name,
                'last_name'  => $dto->last_name,
                'user_name' => $this->uniqueUserName($dto),
                'email'      => $dto->email,
                'password'   => Hash::make($dto->password),
                'current_password' => Crypt::encryptString($dto->password),
                'status'    => 'publish',
                'phone'    => $dto->phone,
                'locale'   => app()->getLocale() ?: 'ru',
            ]);

            $user->assignRole($dto->role);

            $hotel = new Hotel();
            $hotel->title = $dto->base_name;
            $hotel->slug = $this->uniqueHotelSlug($dto->base_name);
            $hotel->status = 'draft';
            $hotel->admin_base = $user->id;
            $hotel->author_id = $user->id;
            $hotel->create_user = $user->id;
            $hotel->save();

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function refresh()
    {

    }

    private function uniqueUserName(RegisterData $dto): string
    {
        $name = trim(($dto->first_name ?? '').' '.($dto->last_name ?? ''));
        $source = $name !== '' ? $name : $dto->base_name;
        $base = Str::slug($source, '_');

        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base;
        $i = 1;

        while (User::query()->where('user_name', $candidate)->exists()) {
            $candidate = $base.'_'.$i;
            $i++;
        }

        return $candidate;
    }

    private function uniqueHotelSlug(string $title): string
    {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'hotel';
        }

        $candidate = $base;
        $i = 1;

        while (Hotel::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
