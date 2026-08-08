<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class AuthPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/login",
        summary: "Аутентификация пользователя",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string", format: "password"),
                ],
                type: "object",
                example: [
                    "email" => "denisburov1982@yandex.ru",
                    "password" => "DjM1w4Pe"
                ]
            )
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешная авторизация",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "user",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "first_name", type: "string", example: "John"),
                                new OA\Property(property: "last_name", type: "string", example: "Doe"),
                                new OA\Property(property: "email", type: "string", example: "john@example.com"),
                                new OA\Property(property: "avatar", type: "string", example: "https://site.com/avatar.jpg"),
                            ],
                            type: "object"
                        ),
                        new OA\Property(property: "token", type: "string", example: "1|abc123"),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                        new OA\Property(property: "expires_in_minutes", type: "integer", example: 60),
                    ]
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            )
        ]
    )]
    public function login(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/logout",
        summary: "Выход пользователя из системы",
        security: [['bearerAuth' => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 204,
                description: "Успешный выход (токен удалён)"
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function logout(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/register",
        description: "Возвращается токен, чтобы после регистрации сразу аутентифицировать пользователя",
        summary: "Регистрация пользователя",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "first_name",
                    "last_name",
                    "email",
                    "password",
                    "phone",
                    "role",
                    "term"
                ],
                properties: [
                    new OA\Property(property: "first_name", type: "string", example: "John"),
                    new OA\Property(property: "last_name", type: "string", example: "Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "test@test.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123"),
                    new OA\Property(property: "phone", type: "string", example: "+7 (996) 579-72-42"),
                    new OA\Property(property: "role", type: "string", example: "baseadmin"),
                    new OA\Property(property: "term", type: "boolean", example: true),
                ],
                type: "object"
            )
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Пользователь успешно зарегистрирован",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
//                        new OA\Property(
//                            property: "user",
//                            properties: [
//                                new OA\Property(property: "id", type: "integer", example: 1),
//                                new OA\Property(property: "first_name", type: "string", example: "John"),
//                                new OA\Property(property: "last_name", type: "string", example: "Doe"),
//                                new OA\Property(property: "email", type: "string", example: "test@test.com"),
//                            ],
//                            type: "object"
//                        ),
                        new OA\Property(property: "token", type: "string", example: "1|abc123"),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                        new OA\Property(property: "expires_in_minutes", type: "integer", example: 60),
                    ]
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            )
        ]
    )]
    public function register(): void
    {}


    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/password/email",
        summary: "Отправить код для сброса пароля",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "user@example.com"
                    ),
                ]
            )
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Код для сброса пароля отправлен",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(),
                            example: []
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            )
        ]
    )]
    public function sendResetCode(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/password/reset",
        summary: "Сброс пароля по коду",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "code", "password", "password_confirmation"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "user@example.com"
                    ),
                    new OA\Property(
                        property: "code",
                        type: "string",
                        example: "123456"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        format: "password",
                        example: "NewStrongPassword123!"
                    ),
                    new OA\Property(
                        property: "password_confirmation",
                        type: "string",
                        format: "password",
                        example: "NewStrongPassword123!"
                    ),
                ]
            )
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Пароль сброшен, возвращён токен авторизации",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "data", type: "string", example: "1|plain-text-token"),
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
    public function resetPassword(): void
    {}
}
