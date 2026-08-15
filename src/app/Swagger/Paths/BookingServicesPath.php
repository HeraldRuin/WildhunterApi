<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class BookingServicesPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services",
        description: "Возвращает каталоги для модалки и уже добавленные услуги. Набор allowed_types зависит от роли и типа брони:\n- Админ базы: trophy, penalty, preparation (если не только проживание), food, addetional\n- Мастер-охотник: preparation (если не только проживание), food, addetional, spending",
        summary: "Услуги бронирования (каталог и сохранённые)",
        security: [['bearerAuth' => []]],
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Каталоги и сохранённые услуги",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["role", "booking_type", "allowed_types", "catalogs", "items"],
                            properties: [
                                new OA\Property(
                                    property: "role",
                                    type: "string",
                                    enum: ["baseadmin", "hunter"]
                                ),
                                new OA\Property(
                                    property: "booking_type",
                                    type: "string",
                                    enum: ["hotel", "animal", "hotel_animal"]
                                ),
                                new OA\Property(
                                    property: "allowed_types",
                                    type: "array",
                                    items: new OA\Items(
                                        type: "string",
                                        enum: ["trophy", "penalty", "preparation", "food", "addetional", "spending"]
                                    )
                                ),
                                new OA\Property(
                                    property: "catalogs",
                                    required: [
                                        "trophy_animals",
                                        "penalty_animals",
                                        "preparation_animals",
                                        "hunters",
                                        "additionals",
                                    ],
                                    properties: [
                                        new OA\Property(
                                            property: "trophy_animals",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "title", "trophies"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer", example: 3),
                                                    new OA\Property(property: "title", type: "string", example: "Кабан"),
                                                    new OA\Property(
                                                        property: "trophies",
                                                        type: "array",
                                                        items: new OA\Items(
                                                            required: ["id", "type"],
                                                            properties: [
                                                                new OA\Property(property: "id", type: "integer"),
                                                                new OA\Property(property: "type", type: "string", example: "Клык"),
                                                            ],
                                                            type: "object"
                                                        )
                                                    ),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "penalty_animals",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "title", "fines"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "title", type: "string"),
                                                    new OA\Property(
                                                        property: "fines",
                                                        type: "array",
                                                        items: new OA\Items(
                                                            required: ["id", "type"],
                                                            properties: [
                                                                new OA\Property(property: "id", type: "integer"),
                                                                new OA\Property(property: "type", type: "string", example: "Ранение"),
                                                            ],
                                                            type: "object"
                                                        )
                                                    ),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "preparation_animals",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "title", "preparations"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "title", type: "string"),
                                                    new OA\Property(
                                                        property: "preparations",
                                                        type: "array",
                                                        items: new OA\Items(
                                                            required: ["id", "type"],
                                                            properties: [
                                                                new OA\Property(property: "id", type: "integer"),
                                                                new OA\Property(property: "type", type: "string"),
                                                            ],
                                                            type: "object"
                                                        )
                                                    ),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "hunters",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "name"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer", example: 12),
                                                    new OA\Property(property: "name", type: "string", example: "Иван Петров"),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "additionals",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "name", "calculation_type", "count", "price"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "name", type: "string", example: "Баня"),
                                                    new OA\Property(
                                                        property: "calculation_type",
                                                        type: "string",
                                                        nullable: true,
                                                        enum: ["individual", "per_person"]
                                                    ),
                                                    new OA\Property(property: "count", type: "integer", nullable: true),
                                                    new OA\Property(property: "price", type: "number", example: 1500),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(
                                    property: "items",
                                    required: [
                                        "trophies",
                                        "penalties",
                                        "preparations",
                                        "foods",
                                        "additionals",
                                        "spendings",
                                    ],
                                    properties: [
                                        new OA\Property(
                                            property: "trophies",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "animal_id", "animal_title", "type", "count"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "animal_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "animal_title", type: "string"),
                                                    new OA\Property(property: "type", type: "string"),
                                                    new OA\Property(property: "count", type: "integer"),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "penalties",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "animal_title", "type", "hunter_id", "hunter_name"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "animal_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "animal_title", type: "string"),
                                                    new OA\Property(property: "type", type: "string"),
                                                    new OA\Property(property: "count", type: "integer", example: 1),
                                                    new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "hunter_name", type: "string"),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "preparations",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "animal_title", "count"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "animal_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "animal_title", type: "string"),
                                                    new OA\Property(property: "count", type: "integer"),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "foods",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "count"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "type", type: "string", example: "Питание"),
                                                    new OA\Property(property: "count", type: "integer"),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "additionals",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "type", "count"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "type", type: "string"),
                                                    new OA\Property(property: "calculation_type", type: "string", nullable: true),
                                                    new OA\Property(property: "count", type: "integer"),
                                                    new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "hunter_name", type: "string"),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "spendings",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "price", "comment"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "price", type: "number"),
                                                    new OA\Property(property: "comment", type: "string"),
                                                    new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "hunter_name", type: "string"),
                                                ],
                                                type: "object"
                                            )
                                        ),
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
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа / тип услуги недоступен для роли"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
        ]
    )]
    public function Services(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/trophies",
        description: "Только администратор базы. Для брони с охотой.",
        summary: "Добавить трофей",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["animal_id", "trophy_id", "type", "count"],
                properties: [
                    new OA\Property(property: "animal_id", type: "integer", example: 3),
                    new OA\Property(property: "trophy_id", type: "integer", example: 8),
                    new OA\Property(property: "type", type: "string", example: "Клык"),
                    new OA\Property(property: "count", type: "integer", example: 1),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Трофей добавлен",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга добавлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "animal_title", "type", "count"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "count", type: "integer"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа / недоступно охотнику"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
            new OA\Response(ref: "#/components/responses/ValidationError", response: 422),
        ]
    )]
    public function StoreTrophy(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/penalties",
        description: "Только администратор базы. Для брони с охотой.",
        summary: "Добавить штраф",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["animal_id", "penalty_id", "type", "hunter_id"],
                properties: [
                    new OA\Property(property: "animal_id", type: "integer", example: 3),
                    new OA\Property(property: "penalty_id", type: "integer", example: 4),
                    new OA\Property(property: "type", type: "string", example: "Ранение"),
                    new OA\Property(property: "hunter_id", type: "integer", example: 12),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Штраф добавлен",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга добавлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "animal_title", "type", "hunter_name"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "count", type: "integer", example: 1),
                                new OA\Property(property: "hunter_id", type: "integer"),
                                new OA\Property(property: "hunter_name", type: "string"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа / недоступно охотнику"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
            new OA\Response(ref: "#/components/responses/ValidationError", response: 422),
        ]
    )]
    public function StorePenalty(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/preparations",
        description: "Админ базы и мастер-охотник. Для брони с охотой. Повторное добавление по тому же животному увеличивает количество.",
        summary: "Добавить разделку",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["animal_id", "preparation_id", "count"],
                properties: [
                    new OA\Property(property: "animal_id", type: "integer", example: 3),
                    new OA\Property(property: "preparation_id", type: "integer", example: 2),
                    new OA\Property(property: "count", type: "integer", example: 1),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Разделка добавлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга добавлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "animal_title", "count"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "count", type: "integer"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
            new OA\Response(ref: "#/components/responses/ValidationError", response: 422),
        ]
    )]
    public function StorePreparation(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/foods",
        description: "Админ базы и мастер-охотник.",
        summary: "Добавить питание",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["count"],
                properties: [
                    new OA\Property(
                        property: "count",
                        description: "Количество человек",
                        type: "integer",
                        example: 2
                    ),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Питание добавлено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга добавлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "count"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "type", type: "string", example: "Питание"),
                                new OA\Property(property: "count", type: "integer"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
            new OA\Response(ref: "#/components/responses/ValidationError", response: 422),
        ]
    )]
    public function StoreFood(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/additionals",
        description: "Админ базы и мастер-охотник. Для calculation_type=individual обязателен hunter_id; для per_person охотник не нужен.",
        summary: "Добавить доп. услугу (другое)",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["additional_id", "name", "count"],
                properties: [
                    new OA\Property(property: "additional_id", type: "integer", example: 7),
                    new OA\Property(property: "name", type: "string", example: "Баня"),
                    new OA\Property(property: "count", type: "integer", example: 1),
                    new OA\Property(
                        property: "hunter_id",
                        description: "Обязателен, если calculation_type услуги — individual",
                        type: "integer",
                        example: 12,
                        nullable: true
                    ),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Доп. услуга добавлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга добавлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "type", "count"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "calculation_type", type: "string", nullable: true),
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                new OA\Property(property: "hunter_name", type: "string"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
            new OA\Response(ref: "#/components/responses/ValidationError", response: 422),
        ]
    )]
    public function StoreAdditional(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/spendings",
        description: "Только мастер-охотник.",
        summary: "Добавить трату охотника",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hunter_id", "price", "comment"],
                properties: [
                    new OA\Property(property: "hunter_id", type: "integer", example: 12),
                    new OA\Property(property: "price", type: "number", example: 500),
                    new OA\Property(property: "comment", type: "string", example: "Патроны"),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Трата добавлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга добавлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "price", "comment", "hunter_name"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "price", type: "number"),
                                new OA\Property(property: "comment", type: "string"),
                                new OA\Property(property: "hunter_id", type: "integer"),
                                new OA\Property(property: "hunter_name", type: "string"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа / недоступно админу базы"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
            new OA\Response(ref: "#/components/responses/ValidationError", response: 422),
        ]
    )]
    public function StoreSpending(): void
    {}

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/{serviceId}",
        description: "Удалить можно только услугу своего набора: админ — trophy/penalty/preparation/food/addetional, охотник — preparation/food/addetional/spending.",
        summary: "Удалить услугу бронирования",
        security: [['bearerAuth' => []]],
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")
            ),
            new OA\Parameter(
                name: "serviceId",
                description: "ID записи bc_booking_services",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 41)
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
                        new OA\Property(property: "data", type: "array", items: new OA\Items()),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(ref: "#/components/responses/AuthResponse", response: 401),
            new OA\Response(response: 403, description: "Нет доступа / тип услуги недоступен для роли"),
            new OA\Response(ref: "#/components/responses/NotFoundResponse", response: 404),
            new OA\Response(response: 409, description: "Добавление услуг недоступно на текущем этапе"),
        ]
    )]
    public function DeleteService(): void
    {}
}
