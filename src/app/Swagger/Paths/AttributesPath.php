<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class AttributesPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/hotels/attributes",
        summary: "Получить список атрибутов для отелей",
        security: [['bearerAuth' => []]],
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
