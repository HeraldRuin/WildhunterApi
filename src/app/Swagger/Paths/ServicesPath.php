<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class ServicesPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/services/additionals",
        description: "Доступно админу базы. Возвращает дополнительные услуги отеля (is_system = false).",
        summary: "Список дополнительных услуг базы",
        security: [['bearerAuth' => []]],
        tags: ["Services"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список услуг",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: [
                                    "id",
                                    "name",
                                    "count",
                                    "calculation_type",
                                    "price",
                                    "type",
                                    "is_system",
                                    "can_delete",
                                    "can_edit_name",
                                ],
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Администратор отеля"),
                                    new OA\Property(property: "count", type: "integer", example: 2, nullable: true),
                                    new OA\Property(
                                        property: "calculation_type",
                                        type: "string",
                                        example: "per_person",
                                        nullable: true,
                                        enum: ["individual", "per_person"]
                                    ),
                                    new OA\Property(property: "price", type: "number", format: "float", example: 0),
                                    new OA\Property(property: "type", type: "string", example: null, nullable: true),
                                    new OA\Property(property: "is_system", type: "boolean", example: false),
                                    new OA\Property(property: "can_delete", type: "boolean", example: true),
                                    new OA\Property(property: "can_edit_name", type: "boolean", example: true),
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
            new OA\Response(
                response: 403,
                description: "Нет прав baseAdmin или у пользователя нет отеля"
            ),
        ]
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/services/system",
        description: "Доступно админу базы. Возвращает системные услуги отеля (is_system = true), например «Питание».",
        summary: "Список системных услуг базы",
        security: [['bearerAuth' => []]],
        tags: ["Services"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список системных услуг",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "name"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 2),
                                    new OA\Property(property: "name", type: "string", example: "Питание"),
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
            new OA\Response(
                response: 403,
                description: "Нет прав baseAdmin или у пользователя нет отеля"
            ),
        ]
    )]
    public function systemIndex(): void
    {
    }

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/services/additionals",
        description: "Создаёт дополнительную услугу каталога. Доступно админу базы. Обязательно передать is_system=false.",
        summary: "Добавить дополнительную услугу",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price", "is_system"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Администратор отеля", maxLength: 255),
                    new OA\Property(property: "price", type: "number", format: "float", example: 0, minimum: 0),
                    new OA\Property(
                        property: "is_system",
                        description: "Тип услуги. При создании всегда false",
                        type: "boolean",
                        example: false
                    ),
                ]
            )
        ),
        tags: ["Services"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Услуга создана",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга сохранена"),
                        new OA\Property(
                            property: "data",
                            required: ["additional"],
                            properties: [
                                new OA\Property(
                                    property: "additional",
                                    required: [
                                        "id",
                                        "name",
                                        "count",
                                        "calculation_type",
                                        "price",
                                        "type",
                                        "is_system",
                                        "can_delete",
                                        "can_edit_name",
                                    ],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 12),
                                        new OA\Property(property: "name", type: "string", example: "Администратор отеля"),
                                        new OA\Property(property: "count", type: "integer", example: null, nullable: true),
                                        new OA\Property(property: "calculation_type", type: "string", example: null, nullable: true),
                                        new OA\Property(property: "price", type: "number", format: "float", example: 0),
                                        new OA\Property(property: "type", type: "string", example: null, nullable: true),
                                        new OA\Property(property: "is_system", type: "boolean", example: false),
                                        new OA\Property(property: "can_delete", type: "boolean", example: true),
                                        new OA\Property(property: "can_edit_name", type: "boolean", example: true),
                                    ],
                                    type: "object"
                                ),
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
                response: 403,
                description: "Нет прав baseAdmin или у пользователя нет отеля"
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

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/services/additionals/{additional}",
        description: "Обновляет название, количество, тип расчёта и стоимость. Для «Питание» меняется только цена. Обязательно передать is_system — значение как в GET (тип услуги менять нельзя).",
        summary: "Сохранить дополнительную услугу",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price", "is_system"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Администратор отеля", maxLength: 255),
                    new OA\Property(property: "price", type: "number", format: "float", example: 1500, minimum: 0),
                    new OA\Property(property: "count", type: "integer", example: 2, nullable: true, minimum: 0),
                    new OA\Property(
                        property: "calculation_type",
                        type: "string",
                        example: "per_person",
                        nullable: true,
                        enum: ["individual", "per_person"]
                    ),
                    new OA\Property(
                        property: "is_system",
                        description: "Как в ответе GET. false — доп. услуга, true — системная (Питание)",
                        type: "boolean",
                        example: false
                    ),
                ]
            )
        ),
        tags: ["Services"],
        parameters: [
            new OA\Parameter(
                name: "additional",
                description: "ID услуги",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Услуга обновлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга обновлена"),
                        new OA\Property(
                            property: "data",
                            required: ["additional"],
                            properties: [
                                new OA\Property(
                                    property: "additional",
                                    required: [
                                        "id",
                                        "name",
                                        "count",
                                        "calculation_type",
                                        "price",
                                        "type",
                                        "is_system",
                                        "can_delete",
                                        "can_edit_name",
                                    ],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "Администратор отеля"),
                                        new OA\Property(property: "count", type: "integer", example: 2, nullable: true),
                                        new OA\Property(
                                            property: "calculation_type",
                                            type: "string",
                                            example: "per_person",
                                            nullable: true,
                                            enum: ["individual", "per_person"]
                                        ),
                                        new OA\Property(property: "price", type: "number", format: "float", example: 1500),
                                        new OA\Property(property: "type", type: "string", example: null, nullable: true),
                                        new OA\Property(property: "is_system", type: "boolean", example: false),
                                        new OA\Property(property: "can_delete", type: "boolean", example: true),
                                        new OA\Property(property: "can_edit_name", type: "boolean", example: true),
                                    ],
                                    type: "object"
                                ),
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
                response: 403,
                description: "Нет прав baseAdmin или у пользователя нет отеля"
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
    public function update(): void
    {
    }

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/services/additionals/{additional}",
        description: "Удаляет услугу. «Питание» удалить нельзя.",
        summary: "Удалить дополнительную услугу",
        security: [['bearerAuth' => []]],
        tags: ["Services"],
        parameters: [
            new OA\Parameter(
                name: "additional",
                description: "ID услуги",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Услуга удалена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга удалена"),
                        new OA\Property(
                            property: "data",
                            required: ["id"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
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
                response: 403,
                description: "Нет прав baseAdmin или у пользователя нет отеля"
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                response: 409,
                description: "Услугу «Питание» удалить нельзя"
            ),
        ]
    )]
    public function destroy(): void
    {
    }
}
