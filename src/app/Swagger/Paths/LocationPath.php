<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class LocationPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/locations/offers",
        description: "Выводит список локаций и количество отелей в ней",
        summary: "Лучшие предложения локаций",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "order_by",
                        type: "string",
                        example: "created_at"
                    ),
                    new OA\Property(
                        property: "order_direction",
                        type: "string",
                        example: "desc",
                        enum: ["asc", "desc"]
                    ),
                    new OA\Property(
                        property: "limit",
                        type: "integer",
                        example: "3"
                    ),
                ]
            )
        ),
        tags: ["Locations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Лучшие локации",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "name", "slug", "image_url", "hotel_count"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                    new OA\Property(property: "image_url", type: "string"),
                                    new OA\Property(property: "hotel_count", type: "integer"),
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
    public function locations(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/locations",
        summary: "Получить список локаций",
        security: [['bearerAuth' => []]],
        tags: ["Locations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список локаций",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "name", "slug"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
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
    public function getLocations(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/locations/{id}/hotels",
        summary: "Выводит список отелей для указанной локации",
        security: [['bearerAuth' => []]],
        tags: ["Locations"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID локации",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer",
                    example: 1,
                    minimum: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список отелей указанной локации",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "slug", "map_lat", "map_lng", "image_url", "star_rate", "price", "review_count", "location"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                    new OA\Property(property: "map_lat", type: "string", nullable: true),
                                    new OA\Property(property: "map_lng", type: "string", nullable: true),
                                    new OA\Property(property: "image_url", type: "string"),
                                    new OA\Property(property: "star_rate", type: "integer", nullable: true),
                                    new OA\Property(property: "price", type: "number", format: "float", nullable: true),
                                    new OA\Property(property: "review_count", type: "integer"),
                                    new OA\Property(
                                        property: "location",
                                        required: ["id", "name", "slug"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "name", type: "string", nullable: true),
                                            new OA\Property(property: "slug", type: "string", nullable: true),
                                        ],
                                        type: "object",
                                        nullable: true
                                    ),
                                ],
                                type: "object"
                            )
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            )
        ]
    )]
    public function getLocationHotels(): void
    {}
}
