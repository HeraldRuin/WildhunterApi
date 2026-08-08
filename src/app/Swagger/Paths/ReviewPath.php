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
        tags: ["Reviews/Ratings"],
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
                response: 200,
                description: "Список отзывов сервиса",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "content", "rate_number", "rate_text", "author", "created_at", "updated_at"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string", nullable: true),
                                    new OA\Property(property: "content", type: "string", nullable: true),
                                    new OA\Property(property: "rate_number", type: "number", format: "float", nullable: true),
                                    new OA\Property(property: "rate_text", type: "string"),
                                    new OA\Property(
                                        property: "author",
                                        required: ["is_guest", "id", "name", "first_name", "last_name", "nik", "avatar_url", "bio"],
                                        properties: [
                                            new OA\Property(property: "is_guest", type: "boolean"),
                                            new OA\Property(property: "id", type: "integer", nullable: true),
                                            new OA\Property(property: "name", type: "string", nullable: true),
                                            new OA\Property(property: "first_name", type: "string", nullable: true),
                                            new OA\Property(property: "last_name", type: "string", nullable: true),
                                            new OA\Property(property: "nik", type: "string", nullable: true),
                                            new OA\Property(property: "avatar_url", type: "string"),
                                            new OA\Property(property: "bio", type: "string", nullable: true),
                                        ],
                                        type: "object",
                                        nullable: true
                                    ),
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
        tags: ["Reviews/Ratings"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список отзывов по типу сервиса",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "content", "rate_number", "rate_text", "author", "created_at", "updated_at"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string", nullable: true),
                                    new OA\Property(property: "content", type: "string", nullable: true),
                                    new OA\Property(property: "rate_number", type: "number", format: "float", nullable: true),
                                    new OA\Property(property: "rate_text", type: "string"),
                                    new OA\Property(
                                        property: "author",
                                        required: ["is_guest", "id", "name", "first_name", "last_name", "nik", "avatar_url", "bio"],
                                        properties: [
                                            new OA\Property(property: "is_guest", type: "boolean"),
                                            new OA\Property(property: "id", type: "integer", nullable: true),
                                            new OA\Property(property: "name", type: "string", nullable: true),
                                            new OA\Property(property: "first_name", type: "string", nullable: true),
                                            new OA\Property(property: "last_name", type: "string", nullable: true),
                                            new OA\Property(property: "nik", type: "string", nullable: true),
                                            new OA\Property(property: "avatar_url", type: "string"),
                                            new OA\Property(property: "bio", type: "string", nullable: true),
                                        ],
                                        type: "object",
                                        nullable: true
                                    ),
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
    public function GetServiceReviews(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/reviews/ratings",
        summary: "Получить список доступных рейтингов",
        tags: ["Reviews/Ratings"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список вариантов оценки",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["value", "label"],
                                properties: [
                                    new OA\Property(property: "value", type: "string", example: "excellent"),
                                    new OA\Property(property: "label", type: "string", example: "Превосходный"),
                                ],
                                type: "object"
                            )
                        ),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function GetRatings(): void
    {}
}
