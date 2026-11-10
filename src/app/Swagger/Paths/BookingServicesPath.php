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
                            required: ["role", "preliminary_total", "booking_type", "allowed_types", "catalogs", "items"],
                            properties: [
                                new OA\Property(
                                    property: "role",
                                    type: "string",
                                    enum: ["baseadmin", "hunter"]
                                ),
                                new OA\Property(
                                    property: "preliminary_total",
                                    description: "Сумма всех услуг брони в рублях. Питание входит уже посчитанной суммой строки и на число суток повторно не умножается. Личные затраты охотников не входят",
                                    type: "integer",
                                    example: 741249
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
                                        "food",
                                    ],
                                    properties: [
                                        new OA\Property(
                                            property: "trophy_animals",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "title", "price", "trophies"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer", example: 3),
                                                    new OA\Property(property: "title", type: "string", example: "Кабан"),
                                                    new OA\Property(
                                                        property: "price",
                                                        description: "Сумма цен типов трофея. null, если цен нет",
                                                        type: "number",
                                                        nullable: true,
                                                        example: 15000
                                                    ),
                                                    new OA\Property(
                                                        property: "trophies",
                                                        type: "array",
                                                        items: new OA\Items(
                                                            required: ["id", "type", "price"],
                                                            properties: [
                                                                new OA\Property(property: "id", type: "integer"),
                                                                new OA\Property(property: "type", type: "string", example: "Клык"),
                                                                new OA\Property(property: "price", type: "number", example: 5000),
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
                                                required: ["id", "title", "price", "fines"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "title", type: "string"),
                                                    new OA\Property(
                                                        property: "price",
                                                        description: "Сумма цен типов штрафа. null, если цен нет",
                                                        type: "number",
                                                        nullable: true,
                                                        example: 15000
                                                    ),
                                                    new OA\Property(
                                                        property: "fines",
                                                        type: "array",
                                                        items: new OA\Items(
                                                            required: ["id", "type", "price"],
                                                            properties: [
                                                                new OA\Property(property: "id", type: "integer"),
                                                                new OA\Property(property: "type", type: "string", example: "Ранение"),
                                                                new OA\Property(property: "price", type: "number", example: 5000),
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
                                                            required: ["id", "type", "price"],
                                                            properties: [
                                                                new OA\Property(property: "id", type: "integer"),
                                                                new OA\Property(property: "type", type: "string"),
                                                                new OA\Property(property: "price", type: "number", example: 5000),
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
                                        new OA\Property(
                                            property: "food",
                                            description: "Типы питания базы, которые включены и имеют цену",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "name", "price"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer", example: 3),
                                                    new OA\Property(property: "name", type: "string", example: "Завтрак"),
                                                    new OA\Property(
                                                        property: "price",
                                                        description: "Цена за одного человека",
                                                        type: "number",
                                                        example: 1500
                                                    ),
                                                    new OA\Property(
                                                        property: "calculation_type",
                                                        type: "string",
                                                        nullable: true,
                                                        enum: ["individual", "per_person"]
                                                    ),
                                                    new OA\Property(
                                                        property: "unit_price",
                                                        description: "Цена на человека за всё проживание. Для типа «На всех» общая сумма делится на число людей в охоте.",
                                                        type: "number",
                                                        nullable: true,
                                                        example: 5000
                                                    ),
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
                                                required: ["id", "animal_id", "animal_title", "type", "count", "price"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "animal_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "animal_title", type: "string"),
                                                    new OA\Property(property: "type", type: "string"),
                                                    new OA\Property(property: "count", type: "integer"),
                                                    new OA\Property(
                                                        property: "hunter_ids",
                                                        type: "array",
                                                        items: new OA\Items(type: "integer")
                                                    ),
                                                    new OA\Property(property: "hunter_name", type: "string"),
                                                    new OA\Property(property: "price", type: "number", example: 5000),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "penalties",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "animal_title", "type", "hunter_id", "hunter_name", "price"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "animal_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "animal_title", type: "string"),
                                                    new OA\Property(property: "type", type: "string"),
                                                    new OA\Property(property: "count", type: "integer", example: 1),
                                                    new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "hunter_name", type: "string"),
                                                    new OA\Property(property: "price", type: "number", example: 5000),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "preparations",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "animal_id", "animal_title", "type", "count", "price"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "animal_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "animal_title", type: "string"),
                                                    new OA\Property(property: "type", type: "string", example: "Шкура"),
                                                    new OA\Property(property: "count", type: "integer"),
                                                    new OA\Property(property: "price", type: "number", example: 5000),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "foods",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "type", "count", "unit_price", "price"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "type", type: "string", example: "Завтрак"),
                                                    new OA\Property(
                                                        property: "calculation_type",
                                                        type: "string",
                                                        nullable: true,
                                                        enum: ["individual", "per_person"]
                                                    ),
                                                    new OA\Property(
                                                        property: "count",
                                                        description: "Сохранённое количество раз",
                                                        type: "integer",
                                                        example: 3
                                                    ),
                                                    new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "hunter_name", type: "string"),
                                                    new OA\Property(
                                                        property: "unit_price",
                                                        description: "Цена за один раз",
                                                        type: "number",
                                                        example: 500
                                                    ),
                                                    new OA\Property(
                                                        property: "price",
                                                        description: "Сумма строки: unit_price × count",
                                                        type: "number",
                                                        example: 1500
                                                    ),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "additionals",
                                            type: "array",
                                            items: new OA\Items(
                                                required: ["id", "type", "count", "price"],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "type", type: "string"),
                                                    new OA\Property(property: "calculation_type", type: "string", nullable: true),
                                                    new OA\Property(property: "count", type: "integer"),
                                                    new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                                    new OA\Property(property: "hunter_name", type: "string"),
                                                    new OA\Property(property: "price", type: "number", example: 1500),
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
                required: ["animal_id", "trophy_id", "type", "count", "hunter_ids"],
                properties: [
                    new OA\Property(property: "animal_id", type: "integer", example: 3),
                    new OA\Property(property: "trophy_id", type: "integer", example: 8),
                    new OA\Property(property: "type", type: "string", example: "Клык"),
                    new OA\Property(property: "count", type: "integer", example: 1),
                    new OA\Property(property: "hunter_ids", type: "array", items: new OA\Items(type: "integer"), example: [12, 15]),
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
                            required: ["id", "animal_title", "type", "count", "price"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(property: "hunter_ids", type: "array", items: new OA\Items(type: "integer")),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(property: "price", type: "number", example: 5000),
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

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/trophies/{id}",
        description: "Только администратор базы. Обновляет уже сохранённый трофей и пересчитывает сумму строки.",
        summary: "Изменить трофей",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["animal_id", "trophy_id", "type", "count", "hunter_ids"],
                properties: [
                    new OA\Property(property: "animal_id", type: "integer", example: 3),
                    new OA\Property(property: "trophy_id", type: "integer", example: 8),
                    new OA\Property(property: "type", type: "string", example: "Клык"),
                    new OA\Property(property: "count", type: "integer", example: 2),
                    new OA\Property(property: "hunter_ids", type: "array", items: new OA\Items(type: "integer"), example: [12, 15]),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(name: "code", description: "Код бронирования", in: "path", required: true, schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")),
            new OA\Parameter(name: "id", description: "ID записи трофея", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 41)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Трофей обновлён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга обновлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "animal_title", "type", "count", "price", "preliminary_total"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(property: "hunter_ids", type: "array", items: new OA\Items(type: "integer")),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(property: "price", description: "Цена за единицу × count", type: "number", example: 10000),
                                new OA\Property(property: "preliminary_total", description: "Сумма услуг брони после изменения", type: "integer", example: 15000),
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
    public function UpdateTrophy(): void
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
                            required: ["id", "animal_title", "type", "hunter_name", "price"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "count", type: "integer", example: 1),
                                new OA\Property(property: "hunter_id", type: "integer"),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(property: "price", type: "number", example: 5000),
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

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/penalties/{id}",
        description: "Только администратор базы. Обновляет уже сохранённый штраф.",
        summary: "Изменить штраф",
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
            new OA\Parameter(name: "code", description: "Код бронирования", in: "path", required: true, schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")),
            new OA\Parameter(name: "id", description: "ID записи штрафа", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 41)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Штраф обновлён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга обновлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "animal_title", "type", "hunter_id", "hunter_name", "price", "preliminary_total"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "count", type: "integer", example: 1),
                                new OA\Property(property: "hunter_id", type: "integer"),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(property: "price", type: "number", example: 5000),
                                new OA\Property(property: "preliminary_total", description: "Сумма услуг брони после изменения", type: "integer", example: 15000),
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
    public function UpdatePenalty(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/preparations",
        description: "Админ базы и мастер-охотник. Для брони с охотой. Тип берётся из справочника разделок. Повторное добавление того же животного и того же типа увеличивает количество; другой тип того же животного — отдельная строка.",
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
                            required: ["id", "animal_id", "animal_title", "type", "count", "price"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string", example: "Шкура"),
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(property: "price", type: "number", example: 5000),
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

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/preparations/{id}",
        description: "Админ базы и мастер-охотник. Обновляет уже сохранённую разделку и пересчитывает сумму строки. Количество заменяется, а не прибавляется.",
        summary: "Изменить разделку",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["animal_id", "preparation_id", "count"],
                properties: [
                    new OA\Property(property: "animal_id", type: "integer", example: 3),
                    new OA\Property(property: "preparation_id", type: "integer", example: 2),
                    new OA\Property(property: "count", type: "integer", example: 2),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(name: "code", description: "Код бронирования", in: "path", required: true, schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")),
            new OA\Parameter(name: "id", description: "ID записи разделки", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 41)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Разделка обновлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга обновлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "animal_id", "animal_title", "type", "count", "price", "preliminary_total"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "animal_id", type: "integer"),
                                new OA\Property(property: "animal_title", type: "string"),
                                new OA\Property(property: "type", type: "string", example: "Шкура"),
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(property: "price", description: "Цена за единицу × count", type: "number", example: 10000),
                                new OA\Property(property: "preliminary_total", description: "Сумма услуг брони после изменения", type: "integer", example: 15000),
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
    public function UpdatePreparation(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/foods",
        description: "Админ базы и мастер-охотник. Для calculation_type=individual обязателен hunter_id; для per_person охотник не нужен.",
        summary: "Добавить питание",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["food_id", "count"],
                properties: [
                    new OA\Property(
                        property: "food_id",
                        description: "ID типа питания из catalogs.food",
                        type: "integer",
                        example: 3
                    ),
                    new OA\Property(
                        property: "count",
                        description: "Количество раз",
                        type: "integer",
                        example: 3
                    ),
                    new OA\Property(
                        property: "hunter_id",
                        description: "Обязателен, если calculation_type питания — individual",
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
                description: "Питание добавлено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга добавлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "type", "count", "unit_price", "price"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "type", type: "string", example: "Завтрак"),
                                new OA\Property(
                                    property: "calculation_type",
                                    type: "string",
                                    nullable: true,
                                    enum: ["individual", "per_person"]
                                ),
                                new OA\Property(
                                    property: "count",
                                    description: "Сохранённое количество раз",
                                    type: "integer",
                                    example: 3
                                ),
                                new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(
                                    property: "unit_price",
                                    description: "Цена за один раз",
                                    type: "number",
                                    example: 500
                                ),
                                new OA\Property(
                                    property: "price",
                                    description: "Сумма строки: unit_price × count",
                                    type: "number",
                                    example: 1500
                                ),
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

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/foods/{id}",
        description: "Админ базы и мастер-охотник. Полное изменение: food_id, count и hunter_id. Для питания «на всех» hunter_id приходит null, прежний охотник сбрасывается. Отдельная правка количества шлёт только count.",
        summary: "Изменить питание",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["count"],
                properties: [
                    new OA\Property(
                        property: "food_id",
                        description: "ID типа питания. Не передаётся, если меняется только количество",
                        type: "integer",
                        example: 3
                    ),
                    new OA\Property(
                        property: "count",
                        description: "Количество раз, целое число от 1",
                        type: "integer",
                        example: 3
                    ),
                    new OA\Property(
                        property: "hunter_id",
                        description: "Обязателен для individual. Для «на всех» null, прежний охотник сбрасывается",
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
            new OA\Parameter(
                name: "id",
                description: "ID записи питания в bc_booking_services",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 41)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Количество обновлено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга обновлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "type", "count", "unit_price", "price", "preliminary_total"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "type", type: "string", example: "Завтрак"),
                                new OA\Property(
                                    property: "calculation_type",
                                    type: "string",
                                    nullable: true,
                                    enum: ["individual", "per_person"]
                                ),
                                new OA\Property(property: "count", type: "integer", example: 3),
                                new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(
                                    property: "unit_price",
                                    description: "Цена за один раз",
                                    type: "number",
                                    example: 500
                                ),
                                new OA\Property(
                                    property: "price",
                                    description: "Сумма строки: unit_price × count",
                                    type: "number",
                                    example: 1500
                                ),
                                new OA\Property(
                                    property: "preliminary_total",
                                    description: "Сумма услуг брони после изменения",
                                    type: "integer",
                                    example: 15000
                                ),
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
    public function UpdateFood(): void
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
                            required: ["id", "type", "count", "price"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "calculation_type", type: "string", nullable: true),
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(property: "price", type: "number", example: 1500),
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

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/additionals/{id}",
        description: "Админ базы и мастер-охотник. Для calculation_type=individual обязателен hunter_id. Для «на всех» hunter_id приходит null, прежний охотник сбрасывается.",
        summary: "Изменить доп. услугу",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["additional_id", "name", "count"],
                properties: [
                    new OA\Property(property: "additional_id", type: "integer", example: 7),
                    new OA\Property(property: "name", type: "string", example: "Баня"),
                    new OA\Property(property: "count", type: "integer", example: 2),
                    new OA\Property(property: "hunter_id", description: "Обязателен для individual. Для «на всех» null", type: "integer", example: 12, nullable: true),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(name: "code", description: "Код бронирования", in: "path", required: true, schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")),
            new OA\Parameter(name: "id", description: "ID записи доп. услуги", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 41)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Доп. услуга обновлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга обновлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "type", "count", "price", "preliminary_total"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "type", type: "string"),
                                new OA\Property(property: "calculation_type", type: "string", nullable: true),
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(property: "price", description: "Цена каталога × count", type: "number", example: 3000),
                                new OA\Property(property: "preliminary_total", description: "Сумма услуг брони после изменения", type: "integer", example: 15000),
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
    public function UpdateAdditional(): void
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

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/spendings/{id}",
        description: "Только мастер-охотник. Обновляет уже сохранённую трату.",
        summary: "Изменить трату охотника",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hunter_id", "price", "comment"],
                properties: [
                    new OA\Property(property: "hunter_id", type: "integer", example: 12),
                    new OA\Property(property: "price", type: "number", example: 1500),
                    new OA\Property(property: "comment", type: "string", example: "Патроны"),
                ],
                type: "object"
            )
        ),
        tags: ["BookingService"],
        parameters: [
            new OA\Parameter(name: "code", description: "Код бронирования", in: "path", required: true, schema: new OA\Schema(type: "string", example: "faa1c65d4b0de02146a27cea429340fb")),
            new OA\Parameter(name: "id", description: "ID записи траты", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 41)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Трата обновлена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Услуга обновлена"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "price", "comment", "preliminary_total"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "price", type: "number"),
                                new OA\Property(property: "comment", type: "string"),
                                new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                new OA\Property(property: "hunter_name", type: "string"),
                                new OA\Property(property: "preliminary_total", description: "Сумма услуг брони после изменения. Личные траты в неё не входят", type: "integer", example: 15000),
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
    public function UpdateSpending(): void
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

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/services/history",
        description: "Журнал действий по услугам этой брони: добавление, изменение и удаление. Записи сохраняются отдельно и остаются после удаления услуги. Набор service_type ограничен ролью так же, как список услуг. Сортировка от новых к старым.",
        summary: "История услуг бронирования",
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
                description: "История услуг",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["items"],
                            properties: [
                                new OA\Property(
                                    property: "items",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "action", "action_label", "created_at"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 15),
                                            new OA\Property(
                                                property: "action",
                                                type: "string",
                                                enum: ["added", "updated", "deleted"],
                                                example: "added"
                                            ),
                                            new OA\Property(property: "action_label", type: "string", example: "Добавлена"),
                                            new OA\Property(
                                                property: "service_type",
                                                type: "string",
                                                enum: ["trophy", "penalty", "preparation", "food", "addetional", "spending", "extra_hunt"],
                                                example: "addetional"
                                            ),
                                            new OA\Property(property: "booking_service_id", type: "integer", nullable: true, example: 41),
                                            new OA\Property(property: "title", type: "string", nullable: true, example: "Баня"),
                                            new OA\Property(property: "calculation_type", type: "string", nullable: true, example: "individual"),
                                            new OA\Property(property: "count", type: "integer", nullable: true, example: 2),
                                            new OA\Property(property: "price", type: "number", nullable: true, example: 4000),
                                            new OA\Property(property: "hunter_id", type: "integer", nullable: true),
                                            new OA\Property(property: "hunter_name", type: "string", nullable: true, example: "Иван Петров"),
                                            new OA\Property(property: "animal_id", type: "integer", nullable: true),
                                            new OA\Property(property: "animal_title", type: "string", nullable: true),
                                            new OA\Property(property: "comment", type: "string", nullable: true),
                                            new OA\Property(property: "place", type: "string", nullable: true, example: "base"),
                                            new OA\Property(property: "user_id", type: "integer", nullable: true, description: "Кто выполнил действие"),
                                            new OA\Property(property: "user_name", type: "string", nullable: true, example: "Админ базы"),
                                            new OA\Property(property: "created_at", type: "string", format: "date-time"),
                                        ],
                                        type: "object"
                                    )
                                ),
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
            new OA\Response(response: 409, description: "Услуги на текущем этапе недоступны"),
        ]
    )]
    public function ServiceHistory(): void
    {}
}
