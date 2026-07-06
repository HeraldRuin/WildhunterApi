<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class HotelsPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/hotels/offers",
        summary: "Лучшие предложения отелей",
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
        tags: ["Hotels"],
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
    public function offers(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/hotels/search",
        summary: "Поиск отелей",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["check_in", "check_out"],
                properties: [
                    new OA\Property(
                        property: "location_id",
                        description: "ID локации",
                        type: "integer",
                        example: 1,
                        nullable: true
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
                        property: "children",
                        description: "Количество детей",
                        type: "integer",
                        example: 0,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "price",
                        description: "Цена",
                        type: "number",
                        format: "float",
                        example: 4000,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "star_rate",
                        description: "Рейтинг",
                        type: "number",
                        format: "float",
                        example: 4,
                        nullable: true
                    ),
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
                        example: 10
                    ),
                ]
            )
        ),
        tags: ["Hotels"],
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
    public function search(): void
    {}

}
