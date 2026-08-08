<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class AttributesPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/services/attributes",
        summary: "Получить список атрибутов для сервиса",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "type",
                        type: "string",
                        example: "hotel",
                    ),
                ]
            )
        ),
        tags: ["Attributes"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список атрибутов",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "name", "slug", "service", "position", "terms"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                    new OA\Property(property: "service", type: "string", nullable: true),
                                    new OA\Property(property: "position", type: "integer", nullable: true),
                                    new OA\Property(
                                        property: "terms",
                                        type: "array",
                                        items: new OA\Items(
                                            required: ["id", "name", "slug", "content", "icon", "image_url"],
                                            properties: [
                                                new OA\Property(property: "id", type: "integer"),
                                                new OA\Property(property: "name", type: "string", nullable: true),
                                                new OA\Property(property: "slug", type: "string", nullable: true),
                                                new OA\Property(property: "content", type: "string", nullable: true),
                                                new OA\Property(property: "icon", type: "string", nullable: true),
                                                new OA\Property(property: "image_url", type: "string"),
                                                new OA\Property(
                                                    property: "translation",
                                                    required: ["id", "origin_id", "locale", "name", "content"],
                                                    properties: [
                                                        new OA\Property(property: "id", type: "integer"),
                                                        new OA\Property(property: "origin_id", type: "integer"),
                                                        new OA\Property(property: "locale", type: "string"),
                                                        new OA\Property(property: "name", type: "string", nullable: true),
                                                        new OA\Property(property: "content", type: "string", nullable: true),
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
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function getAttributes(): void
    {}
}
