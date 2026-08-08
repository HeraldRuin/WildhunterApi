<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class WeaponPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/weapons",
        summary: "Получить все типы оружий",
        security: [['bearerAuth' => []]],
        tags: ["Weapons"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список типов оружия",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "slug", "content", "image_url", "status"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                    new OA\Property(property: "content", type: "string", nullable: true),
                                    new OA\Property(property: "image_url", type: "string", nullable: true),
                                    new OA\Property(property: "status", type: "string", nullable: true),
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
    public function GetWeapons(): void
    {}

     #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/calibers",
        summary: "Получить калибр оружий",
        security: [['bearerAuth' => []]],
        tags: ["Weapons"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список калибров",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "slug", "content", "status"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                    new OA\Property(property: "content", type: "string", nullable: true),
                                    new OA\Property(property: "status", type: "string", nullable: true),
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
    public function GetCalibers(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/user/weapons",
        summary: "Получить оружие пользователя",
        security: [['bearerAuth' => []]],
        tags: ["Weapons"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Оружие пользователя",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["hunter_billet_number", "weapons"],
                            properties: [
                                new OA\Property(property: "hunter_billet_number", type: "string", nullable: true),
                                new OA\Property(
                                    property: "weapons",
                                    type: "array",
                                    items: new OA\Items(
                                        required: [
                                            "id",
                                            "hunter_license_number",
                                            "hunter_license_date",
                                            "weapon_type",
                                            "weapon_type_id",
                                            "caliber",
                                            "caliber_id"
                                        ],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "hunter_license_number", type: "string", nullable: true),
                                            new OA\Property(property: "hunter_license_date", type: "string", nullable: true),
                                            new OA\Property(property: "weapon_type", type: "string", nullable: true),
                                            new OA\Property(property: "weapon_type_id", type: "integer", nullable: true),
                                            new OA\Property(property: "caliber", type: "string", nullable: true),
                                            new OA\Property(property: "caliber_id", type: "integer", nullable: true),
                                        ],
                                        type: "object"
                                    )
                                ),
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
    public function GetUserWeapons(): void
    {}

    #[OA\POST(
        path: "/api/" . ApiConfig::VERSION . "/user/weapons",
        description: "Можно передать только hunter_billet_number, только данные оружия, или оба набора полей.",
        summary: "Сохранить оружие или охотничий билет пользователя",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "hunter_billet_number",
                        type: "string",
                        example: "АБ1234567",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "hunter_license_number",
                        type: "string",
                        example: "7891011",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "hunter_license_date",
                        type: "string",
                        format: "date",
                        example: "2026-01-01",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "weapon_type_id",
                        type: "integer",
                        example: 1,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "caliber_id",
                        type: "integer",
                        example: 1,
                        nullable: true
                    )
                ]
            )
        ),
        tags: ["Weapons"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Оружие или охотничий билет сохранены",
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
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function SaveWeapons(): void
    {}

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/user/weapons/{id}",
        description: "Можно передать только hunter_billet_number, только данные оружия",
        summary: "Обновить оружие или охотничий билет пользователя",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "hunter_billet_number",
                        type: "string",
                        example: "АБ1234567",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "hunter_license_number",
                        type: "string",
                        example: "7891011",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "hunter_license_date",
                        type: "string",
                        format: "date",
                        example: "2026-01-01",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "weapon_type_id",
                        type: "integer",
                        example: 1,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "caliber_id",
                        type: "integer",
                        example: 1,
                        nullable: true
                    )
                ]
            )
        ),
        tags: ["Weapons"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID записи оружия пользователя",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Оружие или охотничий билет обновлены",
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
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function UpdateUserWeapon(): void
    {}

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/user/weapons/{id}",
        summary: "Удалить оружие пользователя",
        security: [['bearerAuth' => []]],
        tags: ["Weapons"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID записи оружия пользователя",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Оружие пользователя удалено",
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
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function DeleteUserWeapon(): void
    {}
}
