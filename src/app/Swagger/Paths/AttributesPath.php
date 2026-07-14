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
                ref: "#/components/responses/SuccessResponse",
                response: 200
            ),
        ]
    )]
    public function getAttributes(): void
    {}
}
