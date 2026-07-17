<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class BookingPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/users/{id}/bookings",
        summary: "Получить все бронирования пользователя",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
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

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings",
        summary: "Создать бронирование",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hotel_id", "check_in", "check_out", "rooms"],
                properties: [
                    new OA\Property(
                        property: "hotel_id",
                        description: "ID отеля",
                        type: "integer",
                        example: 27
                    ),
                    new OA\Property(
                        property: "animal_id",
                        description: "ID животного",
                        type: "integer",
                        example: 5,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "check_in",
                        description: "Дата заезда",
                        type: "string",
                        format: "date",
                        example: "2026-06-24"
                    ),
                    new OA\Property(
                        property: "check_out",
                        description: "Дата выезда",
                        type: "string",
                        format: "date",
                        example: "2026-06-25"
                    ),
                    new OA\Property(
                        property: "adults",
                        description: "Количество взрослых гостей",
                        type: "integer",
                        example: 1
                    ),
                    new OA\Property(
                        property: "hunters",
                        description: "Количество охотников",
                        type: "integer",
                        example: 1
                    ),
                    new OA\Property(
                        property: "rooms",
                        description: "Выбранные номера",
                        type: "array",
                        items: new OA\Items(
                            required: ["room_id", "number"],
                            properties: [
                                new OA\Property(
                                    property: "room_id",
                                    description: "ID номера",
                                    type: "integer",
                                    example: 10
                                ),
                                new OA\Property(
                                    property: "number",
                                    description: "Количество номеров",
                                    type: "integer",
                                    example: 1
                                ),
                            ],
                            type: "object"
                        )
                    ),
                ]
            )
        ),
        tags: ["Bookings"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/SuccessResponse",
                response: 201
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
        ]
    )]
    public function store(): void
    {}
}
