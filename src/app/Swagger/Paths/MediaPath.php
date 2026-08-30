<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class MediaPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/media/store",
        description: "Загружает изображение в media_files. Возвращает id и urls для галереи/обложки базы. Поле multipart: file.",
        summary: "Загрузить изображение",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["file"],
                    properties: [
                        new OA\Property(
                            property: "file",
                            description: "Изображение (jpeg, jpg, png, webp, gif), до 10 МБ",
                            type: "string",
                            format: "binary"
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        tags: ["Media"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Файл загружен",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Файл загружен"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "large", "medium", "thumb"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 251),
                                new OA\Property(property: "large", type: "string"),
                                new OA\Property(property: "medium", type: "string"),
                                new OA\Property(property: "thumb", type: "string"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
        ]
    )]
    public function store(): void
    {
    }
}
