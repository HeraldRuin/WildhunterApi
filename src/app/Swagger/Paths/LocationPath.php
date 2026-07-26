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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
