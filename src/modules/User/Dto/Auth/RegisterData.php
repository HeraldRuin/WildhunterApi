<?php

namespace Modules\User\Dto\Auth;

use Illuminate\Http\Request;

class RegisterData
{
    public function __construct(
        public ?string $first_name,
        public ?string $last_name,
        public string $base_name,
        public string $email,
        public string $password,
        public string $phone,
        public string $role,
        public bool $term,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            first_name: self::blankToNull($data['first_name'] ?? null),
            last_name: self::blankToNull($data['last_name'] ?? null),
            base_name: trim($data['base_name']),
            email: $data['email'],
            password: $data['password'],
            phone: $data['phone'],
            role: $data['role'],
            term: (bool) $data['term'],
        );
    }

    private static function blankToNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
