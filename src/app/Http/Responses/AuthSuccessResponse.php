<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\User\Http\Resources\UserLoginResource;

/**
 * Class ErrorResponse
 *
 */
final class AuthSuccessResponse extends JsonResponse
{
    public function __construct(string $token, User $user, int $status = 200,
        private readonly ?string $code = null,
        private readonly ?string $domain = null,
        private readonly array $replace = [],
    ) {
        $data = [
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in_minutes' => config('sanctum.expiration'),
            'user' => new UserLoginResource($user),
        ];

        $message = $this->resolveMessage();
        if ($message !== null) {
            $data['message'] = $message;
        }

        parent::__construct($data, $status);
    }

    private function resolveMessage(): ?string
    {
        if ($this->code && $this->domain) {
            return __($this->domain . '.successes.' . $this->code, $this->replace);
        }

        return null;
    }
}
