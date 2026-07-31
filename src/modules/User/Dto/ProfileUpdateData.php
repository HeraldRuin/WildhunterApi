<?php

namespace Modules\User\Dto;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ProfileUpdateData
{
    public function __construct(
        public ?string $first_name,
        public ?string $last_name,
        public ?string $nik,
        public ?string $birthday,
        public string $email,
        public ?string $phone,
        public ?string $city,
        public ?string $address,
        public ?string $hunter_billet_number,
        public ?string $bio,
        public ?UploadedFile $avatar,
        public ?int $avatar_id,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            nik: $data['nik'] ?? null,
            birthday: $data['birthday'] ?? null,

            email: $data['email'],

            phone: $data['phone'] ?? null,
            city: $data['city'] ?? null,
            address: $data['address'] ?? null,

            hunter_billet_number: $data['hunter_billet_number'] ?? null,
            bio: $data['bio'] ?? null,

            avatar: $data['avatar'] ?? null,
            avatar_id: isset($data['avatar_id']) ? (int) $data['avatar_id'] : null,
        );
    }
}
