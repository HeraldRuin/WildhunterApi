<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class UserPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/users",
        summary: "Получить пользователей",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список пользователей",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: [
                                    "id", "name", "first_name", "last_name", "nik", "birthday", "email",
                                    "avatar_url", "phone", "city", "address", "role", "bio", "is_verified",
                                    "status", "created_at"
                                ],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string", nullable: true),
                                    new OA\Property(property: "first_name", type: "string", nullable: true),
                                    new OA\Property(property: "last_name", type: "string", nullable: true),
                                    new OA\Property(property: "nik", type: "string", nullable: true),
                                    new OA\Property(property: "birthday", type: "string", nullable: true),
                                    new OA\Property(property: "email", type: "string", format: "email", nullable: true),
                                    new OA\Property(property: "avatar_url", type: "string", format: "uri"),
                                    new OA\Property(property: "phone", type: "string", nullable: true),
                                    new OA\Property(property: "city", type: "string", nullable: true),
                                    new OA\Property(property: "address", type: "string", nullable: true),
                                    new OA\Property(property: "role", type: "string", nullable: true),
                                    new OA\Property(property: "bio", type: "string", nullable: true),
                                    new OA\Property(property: "is_verified", type: "integer", nullable: true),
                                    new OA\Property(property: "status", type: "string", nullable: true),
                                    new OA\Property(property: "created_at", type: "string", nullable: true),
                                ],
                                type: "object"
                            )
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function GetUsers(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/user/{id}",
        summary: "Получить пользователя",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "id пользователя",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные пользователя",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: [
                                "id", "name", "first_name", "last_name", "nik", "birthday", "email",
                                "avatar_url", "phone", "city", "address", "role", "bio", "is_verified",
                                "status", "created_at"
                            ],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "name", type: "string", nullable: true),
                                new OA\Property(property: "first_name", type: "string", nullable: true),
                                new OA\Property(property: "last_name", type: "string", nullable: true),
                                new OA\Property(property: "nik", type: "string", nullable: true),
                                new OA\Property(property: "birthday", type: "string", nullable: true),
                                new OA\Property(property: "email", type: "string", format: "email", nullable: true),
                                new OA\Property(property: "avatar_url", type: "string", format: "uri"),
                                new OA\Property(property: "phone", type: "string", nullable: true),
                                new OA\Property(property: "city", type: "string", nullable: true),
                                new OA\Property(property: "address", type: "string", nullable: true),
                                new OA\Property(property: "role", type: "string", nullable: true),
                                new OA\Property(property: "bio", type: "string", nullable: true),
                                new OA\Property(property: "is_verified", type: "integer", nullable: true),
                                new OA\Property(property: "status", type: "string", nullable: true),
                                new OA\Property(property: "created_at", type: "string", nullable: true),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function GetUser(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/user/avatars",
        summary: "Получить историю аватаров текущего пользователя",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "История аватаров",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "url", "created_at"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "url", type: "string", format: "uri"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
                                ],
                                type: "object"
                            )
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function getAvatarHistory(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/user",
        summary: "Обновить данные пользователя",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["email"],
                    properties: [
                        new OA\Property(property: "first_name", type: "string", example: ""),
                        new OA\Property(property: "last_name", type: "string", example: ""),
                        new OA\Property(property: "nik", type: "string", example: ""),
                        new OA\Property(property: "birthday", type: "string", example: ""),
                        new OA\Property(property: "email", type: "string", example: "test@mail.com"),
                        new OA\Property(property: "phone", type: "string", example: ""),
                        new OA\Property(property: "city", type: "string", example: ""),
                        new OA\Property(property: "address", type: "string", example: ""),
                        new OA\Property(property: "hunter_billet_number", type: "string", example: ""),
                        new OA\Property(property: "bio", type: "string", example: ""),
                        new OA\Property(
                            property: "avatar",
                            description: "Новый файл аватара (jpeg, jpg, png, webp, gif, до 5 МБ)",
                            type: "string",
                            format: "binary"
                        ),
                        new OA\Property(
                            property: "avatar_id",
                            description: "ID ранее загруженного аватара из GET /user/avatars. "
                                . "Передайте avatar_id или avatar, но не оба поля одновременно.",
                            type: "integer",
                            example: 909,
                            nullable: true
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Обновлённые данные пользователя",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            required: [
                                "id", "name", "first_name", "last_name", "nik", "birthday", "email",
                                "avatar_url", "phone", "city", "address", "role", "bio", "is_verified",
                                "status", "created_at"
                            ],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "name", type: "string", nullable: true),
                                new OA\Property(property: "first_name", type: "string", nullable: true),
                                new OA\Property(property: "last_name", type: "string", nullable: true),
                                new OA\Property(property: "nik", type: "string", nullable: true),
                                new OA\Property(property: "birthday", type: "string", nullable: true),
                                new OA\Property(property: "email", type: "string", format: "email", nullable: true),
                                new OA\Property(property: "avatar_url", type: "string", format: "uri"),
                                new OA\Property(property: "phone", type: "string", nullable: true),
                                new OA\Property(property: "city", type: "string", nullable: true),
                                new OA\Property(property: "address", type: "string", nullable: true),
                                new OA\Property(property: "role", type: "string", nullable: true),
                                new OA\Property(property: "bio", type: "string", nullable: true),
                                new OA\Property(property: "is_verified", type: "integer", nullable: true),
                                new OA\Property(property: "status", type: "string", nullable: true),
                                new OA\Property(property: "created_at", type: "string", nullable: true),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function profileUpdate(): void
    {
    }


    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/user/change-password",
        summary: "Смена пароля пользователя",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "new_password", "new_password_confirmation"],
                properties: [
                    new OA\Property(
                        property: "current_password",
                        type: "string",
                        example: "OldPassword123"
                    ),
                    new OA\Property(
                        property: "new_password",
                        type: "string",
                        example: "NewPassword123"
                    ),
                    new OA\Property(
                        property: "new_password_confirmation",
                        type: "string",
                        example: "NewPassword123"
                    ),
                ]
            )
        ),
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Пароль успешно изменён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(), example: []),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function changePassword(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/user/current-password",
        summary: "Получить текущий пароль пользователя",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Текущий пароль пользователя",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["current_password"],
                            properties: [
                                new OA\Property(property: "current_password", type: "string", nullable: true),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function getCurrentPassword(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/user/newsletter/subscribe",
        summary: "Подписка на рассылку",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "privacy_policy"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "user@example.com"
                    ),
                    new OA\Property(
                        property: "privacy_policy",
                        type: "boolean",
                        example: true
                    ),
                ]
            )
        ),
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Подписка на рассылку оформлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(), example: []),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function subscribeNewsletter(): void
    {}
}
