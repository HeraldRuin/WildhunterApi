<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class RoomsPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/rooms/{id}",
        summary: "Получить информацию о номере",
        security: [['bearerAuth' => []]],
        tags: ["Rooms"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID отеля",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer",
                    example: 27,
                    minimum: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                ref: "#/components/responses/SuccessResponse",
                response: 200
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
        ]
    )]
    public function show(): void
    {
    }

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/hotels/rooms/check-availability",
        summary: "Проверка доступности номеров отеля",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hotel_id", "check_in", "check_out", "adults"],
                properties: [
                    new OA\Property(
                        property: "hotel_id",
                        description: "ID отеля",
                        type: "integer",
                        example: 27,
                        minimum: 1
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
                        example: 2,
                        minimum: 1
                    ),
                ]
            )
        ),
        tags: ["Rooms"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/SuccessResponse",
                response: 200
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
        ]
    )]
    public function checkAvailability(): void
    {
    }
}
