<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class AnimalsPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/animals",
        summary: "Получить список животных",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
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
    public function getAnimals(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/animals/check-availability",
        summary: "Проверка доступности животного на базе",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hotel_id", "animal_id", "hunter_data", "hunters"],
                properties: [
                    new OA\Property(
                        property: "hotel_id",
                        description: "ID отеля",
                        type: "integer",
                        example: 27,
                        minimum: 1
                    ),
                    new OA\Property(
                        property: "animal_id",
                        description: "ID животного",
                        type: "integer",
                        example: 5,
                        minimum: 1
                    ),
                    new OA\Property(
                        property: "hunter_data",
                        description: "Дата охоты",
                        type: "string",
                        format: "date",
                        example: "2026-06-24"
                    ),
                    new OA\Property(
                        property: "hunters",
                        description: "Количество взрослых охотников",
                        type: "integer",
                        example: 1,
                        minimum: 1
                    ),
                    new OA\Property(
                        property: "check_in",
                        description: "Дата заезда (опционально, для проверки что дата охоты в пределах проживания)",
                        type: "string",
                        format: "date",
                        example: "2026-06-24",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "check_out",
                        description: "Дата выезда (опционально, для проверки что дата охоты в пределах проживания)",
                        type: "string",
                        format: "date",
                        example: "2026-06-25",
                        nullable: true
                    ),
                ]
            )
        ),
        tags: ["Animals"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
    {}
}
