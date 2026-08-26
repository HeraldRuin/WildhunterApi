<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class CollectionPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/start-collection",
        summary: "Запустить сбор охотников",
        security: [['bearerAuth' => []]],
        tags: ["Collection"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "faa1c65d4b0de02146a27cea429340fb"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Сбор охотников запущен",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Сбор охотников начат"
                        ),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 222),
                                new OA\Property(
                                    property: "code",
                                    type: "string",
                                    example: "faa1c65d4b0de02146a27cea429340fb"
                                ),
                                new OA\Property(property: "status", type: "string", example: "collection"),
                                new OA\Property(
                                    property: "collection_start_at",
                                    type: "string",
                                    format: "date-time",
                                    example: "2026-08-08T12:31:00+03:00"
                                ),
                                new OA\Property(
                                    property: "collection_end_at",
                                    type: "string",
                                    format: "date-time",
                                    example: "2026-08-09T12:31:00+03:00"
                                ),
                                new OA\Property(
                                    property: "collection_timer_hours",
                                    type: "integer",
                                    example: 24
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
                description: "Пользователь не является мастер-охотником этой брони"
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                response: 409,
                description: "Бронь не находится в статусе confirmed"
            ),
        ]
    )]
    public function StartCollection(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/extend-collection",
        summary: "Продлить сбор охотников",
        security: [['bearerAuth' => []]],
        tags: ["Collection"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "faa1c65d4b0de02146a27cea429340fb"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Сбор охотников продлён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Сбор охотников продлён"
                        ),
                        new OA\Property(
                            property: "data",
                            required: [
                                "id",
                                "code",
                                "status",
                                "collection_start_at",
                                "collection_end_at",
                                "collection_timer_hours",
                            ],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 222),
                                new OA\Property(
                                    property: "code",
                                    type: "string",
                                    example: "faa1c65d4b0de02146a27cea429340fb"
                                ),
                                new OA\Property(property: "status", type: "string", example: "collection"),
                                new OA\Property(
                                    property: "collection_start_at",
                                    type: "string",
                                    format: "date-time",
                                    example: "2026-08-08T21:44:00+03:00"
                                ),
                                new OA\Property(
                                    property: "collection_end_at",
                                    type: "string",
                                    format: "date-time",
                                    example: "2026-08-09T21:44:00+03:00"
                                ),
                                new OA\Property(
                                    property: "collection_timer_hours",
                                    type: "integer",
                                    example: 24
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
                description: "Пользователь не является мастер-охотником этой брони"
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                response: 409,
                description: "Сбор не активен, таймер отсутствует или ещё не истёк"
            ),
        ]
    )]
    public function ExtendCollection(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/finish-collection",
        summary: "Завершить сбор охотников",
        security: [['bearerAuth' => []]],
        tags: ["Collection"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "faa1c65d4b0de02146a27cea429340fb"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Сбор охотников завершён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Сбор охотников завершён"
                        ),
                        new OA\Property(
                            property: "data",
                            required: ["id", "code", "status"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 222),
                                new OA\Property(
                                    property: "code",
                                    type: "string",
                                    example: "faa1c65d4b0de02146a27cea429340fb"
                                ),
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "finished_collection",
                                    enum: ["finished_collection", "prepayment_collection"]
                                ),
                                new OA\Property(
                                    property: "paid_start_at",
                                    description: "Возвращается для бронирования с проживанием",
                                    type: "string",
                                    format: "date-time",
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "paid_end_at",
                                    description: "Возвращается для бронирования с проживанием",
                                    type: "string",
                                    format: "date-time",
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "paid_timer_hours",
                                    description: "Возвращается для бронирования с проживанием",
                                    type: "integer",
                                    nullable: true,
                                    example: 24
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
                description: "Пользователь не является мастер-охотником этой брони"
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                response: 409,
                description: "Сбор не активен, приглашено недостаточно охотников или не все подтвердили участие"
            ),
        ]
    )]
    public function FinishCollection(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/expire-prepayment",
        summary: "Зафиксировать окончание таймера предоплаты",
        security: [['bearerAuth' => []]],
        tags: ["Collection"],
        parameters: [
            new OA\Parameter(
                name: "code",
                description: "Код бронирования",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "faa1c65d4b0de02146a27cea429340fb"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Неоплаченные приглашения обновлены",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: ""
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items()
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
                description: "Пользователь не является мастер-охотником этой брони"
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                response: 409,
                description: "Сбор предоплаты не активен, таймер отсутствует или ещё не завершён"
            ),
        ]
    )]
    public function ExpirePrepayment(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/settings/timers/{type}",
        description: "Типы: collection (сбор), beds (койко-места), prepayment (предоплата). Значение хранится в bc_hotels.",
        summary: "Получить настройки таймера отеля",
        security: [['bearerAuth' => []]],
        tags: ["Collection"],
        parameters: [
            new OA\Parameter(
                name: "type",
                description: "Тип таймера",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "collection",
                    enum: ["collection", "beds", "prepayment"]
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Настройки таймера",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["type", "timer_hours", "hotel_id"],
                            properties: [
                                new OA\Property(
                                    property: "type",
                                    type: "string",
                                    example: "collection",
                                    enum: ["collection", "beds", "prepayment"]
                                ),
                                new OA\Property(property: "timer_hours", type: "integer", example: 24),
                                new OA\Property(property: "hotel_id", type: "integer", example: 1),
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
        ]
    )]
    public function GetTimerSettings(): void
    {}

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/settings/timers/{type}",
        description: "Типы: collection (сбор), beds (койко-места), prepayment (предоплата). Значение пишется в bc_hotels.",
        summary: "Сохранить настройки таймера отеля",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["timer_hours"],
                properties: [
                    new OA\Property(
                        property: "timer_hours",
                        description: "Размер таймера в часах",
                        type: "integer",
                        example: 24,
                        minimum: 1
                    ),
                ],
                type: "object"
            )
        ),
        tags: ["Collection"],
        parameters: [
            new OA\Parameter(
                name: "type",
                description: "Тип таймера",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "collection",
                    enum: ["collection", "beds", "prepayment"]
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Настройки сохранены",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Настройки таймера успешно сохранены"
                        ),
                        new OA\Property(
                            property: "data",
                            required: ["type", "timer_hours", "hotel_id"],
                            properties: [
                                new OA\Property(
                                    property: "type",
                                    type: "string",
                                    example: "collection",
                                    enum: ["collection", "beds", "prepayment"]
                                ),
                                new OA\Property(property: "timer_hours", type: "integer", example: 24),
                                new OA\Property(property: "hotel_id", type: "integer", example: 1),
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
    public function StoreTimerSettings(): void
    {}
}
