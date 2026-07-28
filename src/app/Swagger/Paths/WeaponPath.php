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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
        summary: "Сохранить оружие для пользователя",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hunter_license_number", "hunter_license_date", "weapon_type_id", "caliber_id"],
                properties: [
                    new OA\Property(
                        property: "hunter_license_number",
                        type: "string",
                        example: "7891011"
                    ),
                    new OA\Property(
                        property: "hunter_license_date",
                        type: "string",
                        format: "date",
                        example: "2026-01-01"
                    ),
                    new OA\Property(
                        property: "weapon_type_id",
                        type: "integer",
                        example: 1
                    ),
                    new OA\Property(
                        property: "caliber_id",
                        type: "integer",
                        example: 1
                    )
                ]
            )
        ),
        tags: ["Weapons"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/SuccessResponse",
                response: 200
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function SaveWeapons(): void
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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
