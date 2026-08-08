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
                response: 200,
                description: "Доступные номера",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["rooms"],
                            properties: [
                                new OA\Property(
                                    property: "rooms",
                                    type: "array",
                                    items: new OA\Items(
                                        required: [
                                            "id", "title", "price", "nights", "size", "beds",
                                            "adults", "children", "number_selected", "number",
                                            "image_url", "gallery",
                                        ],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "title", type: "string", nullable: true),
                                            new OA\Property(property: "price", type: "number", format: "float"),
                                            new OA\Property(property: "nights", type: "integer"),
                                            new OA\Property(property: "size", type: "integer", nullable: true),
                                            new OA\Property(property: "beds", type: "integer", nullable: true),
                                            new OA\Property(property: "adults", type: "integer", nullable: true),
                                            new OA\Property(property: "children", type: "integer", nullable: true),
                                            new OA\Property(property: "number_selected", type: "integer"),
                                            new OA\Property(property: "number", type: "integer"),
                                            new OA\Property(property: "image_url", type: "string", format: "uri"),
                                            new OA\Property(
                                                property: "gallery",
                                                type: "array",
                                                items: new OA\Items(
                                                    required: ["large", "medium", "thumb"],
                                                    properties: [
                                                        new OA\Property(property: "large", type: "string", format: "uri"),
                                                        new OA\Property(property: "medium", type: "string", format: "uri"),
                                                        new OA\Property(property: "thumb", type: "string", format: "uri"),
                                                    ],
                                                    type: "object"
                                                )
                                            ),
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
