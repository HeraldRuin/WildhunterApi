<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use App\Swagger\Responses\ValidationError;
use OpenApi\Attributes as OA;

class RolePath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/roles",
        summary: "Получить роли",
        security: [["bearerAuth" => []]],
        tags: ["Roles/Permissions"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список ролей",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "code", "name", "created_at", "updated_at"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "code", type: "string"),
                                    new OA\Property(property: "name", type: "string"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
                                    new OA\Property(property: "updated_at", type: "string", format: "date-time", nullable: true),
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
        path: "/api/" . ApiConfig::VERSION . "/roles/{id}",
        summary: "Получить роль по id",
        security: [["bearerAuth" => []]],
        tags: ["Roles/Permissions"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "id роли",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные роли или null",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["id", "code", "name", "created_at", "updated_at"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
                                new OA\Property(property: "updated_at", type: "string", format: "date-time", nullable: true),
                            ],
                            type: "object",
                            nullable: true
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
    public function getById(): void {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/roles/code/{code}",
        summary: "Получить роль по коду",
        security: [["bearerAuth" => []]],
        tags: ["Roles/Permissions"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "code роли",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные роли или null",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["id", "code", "name", "created_at", "updated_at"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
                                new OA\Property(property: "updated_at", type: "string", format: "date-time", nullable: true),
                            ],
                            type: "object",
                            nullable: true
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
    public function getByCode(): void {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/roles/user/{id}",
        summary: "Получить роль пользователя",
        security: [["bearerAuth" => []]],
        tags: ["Roles/Permissions"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "id пользователя",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные роли пользователя или null",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["id", "code", "name", "created_at", "updated_at"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
                                new OA\Property(property: "updated_at", type: "string", format: "date-time", nullable: true),
                            ],
                            type: "object",
                            nullable: true
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
    public function getUserRole(): void {}



}
