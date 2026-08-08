<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class WhiteListPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/services/{id}/favorites",
        summary: "Является ли сервис избранным для пользователя",
        security: [['bearerAuth' => []]],
        tags: ["WhiteList"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "id сервиса",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            ),
            new OA\Parameter(
                name: "type",
                description: "тип сервиса",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "hotel"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Статус нахождения в избранном",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["is_in_wishList"],
                            properties: [
                                new OA\Property(property: "is_in_wishList", type: "boolean"),
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
    public function getFavorites(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/services/favorites",
        summary: "Получить избранное пользователя",
        security: [['bearerAuth' => []]],
        tags: ["WhiteList"],
        parameters: [
            new OA\Parameter(
                name: "type",
                description: "тип сервиса",
                in: "query",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "hotel"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список избранного",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["service_id", "service_model", "user_id"],
                                properties: [
                                    new OA\Property(property: "service_id", type: "integer"),
                                    new OA\Property(property: "service_model", type: "string", example: "hotel"),
                                    new OA\Property(property: "user_id", type: "integer"),
                                ],
                                type: "object"
                            )
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
    public function getUserFavorites(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/services/{id}/favorite",
        summary: "Добавить сервис в избранное",
        security: [['bearerAuth' => []]],
        tags: ["WhiteList"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "id сервиса",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            ),
            new OA\Parameter(
                name: "type",
                description: "тип сервиса",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "hotel"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Сервис добавлен в избранное",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            required: ["service_id", "service_model", "user_id"],
                            properties: [
                                new OA\Property(property: "service_id", type: "integer"),
                                new OA\Property(property: "service_model", type: "string", example: "hotel"),
                                new OA\Property(property: "user_id", type: "integer"),
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
    public function addFavorite(): void
    {}

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/services/{id}/favorite",
        summary: "Удалить сервис из избранного",
        security: [['bearerAuth' => []]],
        tags: ["WhiteList"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "id сервиса",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            ),
            new OA\Parameter(
                name: "type",
                description: "тип сервиса",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "hotel"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Обновлённый список избранного",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["service_id", "service_model", "user_id"],
                                properties: [
                                    new OA\Property(property: "service_id", type: "integer"),
                                    new OA\Property(property: "service_model", type: "string", example: "hotel"),
                                    new OA\Property(property: "user_id", type: "integer"),
                                ],
                                type: "object"
                            )
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
    public function removeFavorite(): void
    {}
}
