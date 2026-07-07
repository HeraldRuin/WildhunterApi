<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class ReviewPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/services/{id}/reviews",
        summary: "Получить все отзывы конкретного сервиса",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "type",
                        type: "string",
                        example: "hotel",
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
                        example: "3"
                    ),
                ]
            )
        ),
        tags: ["Reviews"],
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
    public function GetReviews(): void
    {}

     #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/services/reviews",
        summary: "Получить все отзывы для определенного типа сервиса",
         requestBody: new OA\RequestBody(
             content: new OA\JsonContent(
                 properties: [
                     new OA\Property(
                         property: "type",
                         type: "string",
                         example: "hotel",
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
                         example: "3"
                     ),
                 ]
             )
         ),
        tags: ["Reviews"],
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
    public function GetServiceReviews(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/reviews/ratings",
        summary: "Получить список доступных рейтингов",
        tags: ["Reviews"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/SuccessResponse",
                response: 200
            ),
        ]
    )]
    public function GetRatings(): void
    {}
}
