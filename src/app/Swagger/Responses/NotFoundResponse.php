<?php

namespace App\Swagger\Responses;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: "NotFoundResponse",
    description: "Ресурс не найден",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: "success",
                type: "boolean",
                example: false
            ),
            new OA\Property(
                property: "message",
                type: "string",
                example: "Запрашиваемый ресурс не найден"
            ),
            new OA\Property(
                property: "error_code",
                type: "string",
                example: "not_found"
            ),
        ]
    )
)]
class NotFoundResponse {}
