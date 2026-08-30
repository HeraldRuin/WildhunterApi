<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class RoomsPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/rooms",
        description: "Доступно админу базы. Аналог страницы user/hotel/{id}/availability — вкладки номеров слева.",
        summary: "Список номеров отеля для календаря",
        security: [['bearerAuth' => []]],
        tags: ["Rooms"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список номеров",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["hotel_id", "rooms"],
                            properties: [
                                new OA\Property(property: "hotel_id", type: "integer", example: 27),
                                new OA\Property(
                                    property: "rooms",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "title", "number", "price", "status"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 12),
                                            new OA\Property(property: "title", type: "string", example: "Стандарт"),
                                            new OA\Property(property: "number", type: "integer", example: 3),
                                            new OA\Property(property: "price", type: "number", format: "float", example: 5000),
                                            new OA\Property(property: "status", type: "string", example: "publish"),
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
        path: "/api/" . ApiConfig::VERSION . "/rooms/availability",
        description: "Аналог loadDates в booking_core. id=summary — сводный календарь, иначе ID номера. Realtime: private channel hotel.{hotel_id}.room-availability, event .room.availability.updated (создана/отменена бронь с номерами) — после события перезапросить этот endpoint.",
        summary: "Календарь доступности номеров",
        security: [['bearerAuth' => []]],
        tags: ["Rooms"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID номера или summary для сводного календаря",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string", example: "summary")
            ),
            new OA\Parameter(
                name: "start",
                description: "Начало периода",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string", format: "date", example: "2026-08-01")
            ),
            new OA\Parameter(
                name: "end",
                description: "Конец периода",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string", format: "date", example: "2026-08-31")
            ),
            new OA\Parameter(
                name: "for_single",
                description: "Формат цены для одиночного вида",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "boolean", example: false)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Дни календаря доступности",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "start", "allDay", "price", "number", "active", "title", "extendedProps"],
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "start", type: "string", format: "date", example: "2026-08-15"),
                                    new OA\Property(property: "allDay", type: "boolean", example: true),
                                    new OA\Property(property: "price", type: "number", format: "float", example: 5000),
                                    new OA\Property(property: "number", type: "integer", example: 2),
                                    new OA\Property(property: "active", type: "integer", example: 1),
                                    new OA\Property(property: "title", type: "string", example: "5.000 руб x 2"),
                                    new OA\Property(
                                        property: "occupiedRooms",
                                        type: "integer",
                                        example: 1,
                                        nullable: true
                                    ),
                                    new OA\Property(
                                        property: "is_checkout_day",
                                        type: "boolean",
                                        example: false,
                                        nullable: true
                                    ),
                                    new OA\Property(
                                        property: "extendedProps",
                                        required: ["max_number"],
                                        properties: [
                                            new OA\Property(property: "max_number", type: "integer", example: 3),
                                            new OA\Property(property: "price_changed", type: "boolean", example: false),
                                            new OA\Property(property: "number_changed", type: "boolean", example: false),
                                            new OA\Property(property: "is_summary", type: "boolean", example: false),
                                        ],
                                        type: "object"
                                    ),
                                    new OA\Property(
                                        property: "bookings",
                                        type: "array",
                                        items: new OA\Items(
                                            required: ["id", "booking_number", "code", "status", "statusName", "is_checkout"],
                                            properties: [
                                                new OA\Property(property: "id", type: "integer"),
                                                new OA\Property(property: "booking_number", type: "string", nullable: true),
                                                new OA\Property(property: "code", type: "string"),
                                                new OA\Property(property: "status", type: "string"),
                                                new OA\Property(property: "statusName", type: "string"),
                                                new OA\Property(property: "is_checkout", type: "boolean", example: false),
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
    public function loadDates(): void
    {
    }

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/hotels/rooms/check-availability",
        description: "Поиск свободных номеров по датам и числу гостей (карточка отеля / бронирование). Не календарь кабинета.",
        summary: "Проверка доступности номеров отеля",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hotel_id", "check_in", "check_out", "adults"],
                properties: [
                    new OA\Property(
                        property: "hotel_id",
                        description: "ID отеля",
                        type: "integer",
                        example: 27,
                        minimum: 1
                    ),
                    new OA\Property(
                        property: "check_in",
                        description: "Дата заезда",
                        type: "string",
                        format: "date",
                        example: "2026-06-24"
                    ),
                    new OA\Property(
                        property: "check_out",
                        description: "Дата выезда",
                        type: "string",
                        format: "date",
                        example: "2026-06-25"
                    ),
                    new OA\Property(
                        property: "adults",
                        description: "Количество взрослых гостей",
                        type: "integer",
                        example: 2,
                        minimum: 1
                    ),
                ]
            )
        ),
        tags: ["Rooms"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Доступные номера",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["rooms"],
                            properties: [
                                new OA\Property(
                                    property: "rooms",
                                    type: "array",
                                    items: new OA\Items(
                                        required: [
                                            "id", "title", "price", "nights", "size", "beds",
                                            "adults", "children", "number_selected", "number",
                                            "image_url", "gallery", "attributes",
                                        ],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "title", type: "string", nullable: true),
                                            new OA\Property(property: "price", type: "number", format: "float"),
                                            new OA\Property(property: "nights", type: "integer"),
                                            new OA\Property(property: "size", type: "integer", nullable: true),
                                            new OA\Property(property: "beds", type: "integer", nullable: true),
                                            new OA\Property(property: "adults", type: "integer", nullable: true),
                                            new OA\Property(property: "children", type: "integer", nullable: true),
                                            new OA\Property(property: "number_selected", type: "integer"),
                                            new OA\Property(property: "number", type: "integer"),
                                            new OA\Property(property: "image_url", type: "string", format: "uri"),
                                            new OA\Property(
                                                property: "gallery",
                                                type: "array",
                                                items: new OA\Items(
                                                    required: ["large", "medium", "thumb"],
                                                    properties: [
                                                        new OA\Property(property: "large", type: "string", format: "uri"),
                                                        new OA\Property(property: "medium", type: "string", format: "uri"),
                                                        new OA\Property(property: "thumb", type: "string", format: "uri"),
                                                    ],
                                                    type: "object"
                                                )
                                            ),
                                            new OA\Property(
                                                property: "attributes",
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
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
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
    {
    }

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/rooms",
        description: "Доступно админу базы. Создаёт номер у отеля текущего пользователя (parent_id). Обязательно только title. Статус по умолчанию — draft. В ответе — данные номера в формате формы редактирования.",
        summary: "Создать номер",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Люкс", maxLength: 255),
                    new OA\Property(property: "content", type: "string", nullable: true),
                    new OA\Property(property: "image_id", type: "integer", example: 120, nullable: true),
                    new OA\Property(
                        property: "gallery",
                        description: "Массив ID файлов из media_files",
                        type: "array",
                        items: new OA\Items(type: "integer", example: 121),
                        nullable: true
                    ),
                    new OA\Property(property: "price", type: "number", format: "float", example: 4500, nullable: true, minimum: 0),
                    new OA\Property(property: "number", type: "integer", example: 3, nullable: true, minimum: 1, description: "Количество экземпляров этого типа номера"),
                    new OA\Property(property: "beds", type: "integer", example: 2, nullable: true, minimum: 0),
                    new OA\Property(property: "size", type: "integer", example: 28, nullable: true, minimum: 0, description: "Площадь, м²"),
                    new OA\Property(property: "adults", type: "integer", example: 2, nullable: true, minimum: 1),
                    new OA\Property(property: "children", type: "integer", example: 1, nullable: true, minimum: 0),
                    new OA\Property(
                        property: "status",
                        type: "string",
                        example: "draft",
                        nullable: true,
                        enum: ["publish", "draft", "pending"]
                    ),
                    new OA\Property(property: "min_day_stays", type: "integer", example: 1, nullable: true, minimum: 1),
                    new OA\Property(property: "ical_import_url", type: "string", nullable: true, maxLength: 255),
                    new OA\Property(property: "video", type: "string", nullable: true, maxLength: 255, description: "URL видео (YouTube и т.п.)"),
                    new OA\Property(
                        property: "term_ids",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 5, 12],
                        nullable: true
                    ),
                ]
            )
        ),
        tags: ["Rooms"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Номер создан",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Номер создан"),
                        new OA\Property(
                            property: "data",
                            description: "Те же поля, что у GET /rooms/{room}",
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
    public function manageStore(): void
    {
    }

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/rooms/{room}",
        description: "Доступно админу базы. Возвращает данные своего номера для формы редактирования. Номер должен принадлежать отелю текущего пользователя.",
        summary: "Данные номера для редактирования",
        security: [['bearerAuth' => []]],
        tags: ["Rooms"],
        parameters: [
            new OA\Parameter(
                name: "room",
                description: "ID номера",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 12)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные номера для редактирования",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: [
                                "id",
                                "title",
                                "content",
                                "image_id",
                                "image_url",
                                "gallery",
                                "price",
                                "number",
                                "beds",
                                "size",
                                "adults",
                                "children",
                                "status",
                                "status_label",
                                "min_day_stays",
                                "ical_import_url",
                                "video",
                                "term_ids",
                            ],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 12),
                                new OA\Property(property: "title", type: "string", example: "Люкс", nullable: true),
                                new OA\Property(property: "content", type: "string", nullable: true),
                                new OA\Property(property: "image_id", type: "integer", example: 120, nullable: true),
                                new OA\Property(property: "image_url", type: "string"),
                                new OA\Property(
                                    property: "gallery",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "large", "medium", "thumb"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 121),
                                            new OA\Property(property: "large", type: "string"),
                                            new OA\Property(property: "medium", type: "string"),
                                            new OA\Property(property: "thumb", type: "string"),
                                        ],
                                        type: "object"
                                    )
                                ),
                                new OA\Property(property: "price", type: "number", format: "float", example: 4500, nullable: true),
                                new OA\Property(property: "number", type: "integer", example: 3, nullable: true),
                                new OA\Property(property: "beds", type: "integer", example: 2, nullable: true),
                                new OA\Property(property: "size", type: "integer", example: 28, nullable: true),
                                new OA\Property(property: "adults", type: "integer", example: 2, nullable: true),
                                new OA\Property(property: "children", type: "integer", example: 1, nullable: true),
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "publish",
                                    enum: ["publish", "draft", "pending", "trash"]
                                ),
                                new OA\Property(property: "status_label", type: "string", example: "Опубликован"),
                                new OA\Property(property: "min_day_stays", type: "integer", example: 1, nullable: true),
                                new OA\Property(property: "ical_import_url", type: "string", nullable: true),
                                new OA\Property(property: "video", type: "string", nullable: true),
                                new OA\Property(
                                    property: "term_ids",
                                    type: "array",
                                    items: new OA\Items(type: "integer"),
                                    example: [1, 5, 12]
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
        ]
    )]
    public function manageShow(): void
    {
    }

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/rooms/{room}",
        description: "Доступно админу базы. Сохраняет данные своего номера из формы редактирования. В ответе — актуальные данные номера в том же формате, что и GET.",
        summary: "Сохранить номер",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Люкс", maxLength: 255),
                    new OA\Property(property: "content", type: "string", nullable: true),
                    new OA\Property(property: "image_id", type: "integer", example: 120, nullable: true),
                    new OA\Property(
                        property: "gallery",
                        description: "Массив ID файлов из media_files",
                        type: "array",
                        items: new OA\Items(type: "integer", example: 121),
                        nullable: true
                    ),
                    new OA\Property(property: "price", type: "number", format: "float", example: 4500, nullable: true, minimum: 0),
                    new OA\Property(property: "number", type: "integer", example: 3, nullable: true, minimum: 1),
                    new OA\Property(property: "beds", type: "integer", example: 2, nullable: true, minimum: 0),
                    new OA\Property(property: "size", type: "integer", example: 28, nullable: true, minimum: 0),
                    new OA\Property(property: "adults", type: "integer", example: 2, nullable: true, minimum: 1),
                    new OA\Property(property: "children", type: "integer", example: 1, nullable: true, minimum: 0),
                    new OA\Property(
                        property: "status",
                        type: "string",
                        example: "publish",
                        nullable: true,
                        enum: ["publish", "draft", "pending"]
                    ),
                    new OA\Property(property: "min_day_stays", type: "integer", example: 1, nullable: true, minimum: 1),
                    new OA\Property(property: "ical_import_url", type: "string", nullable: true, maxLength: 255),
                    new OA\Property(property: "video", type: "string", nullable: true, maxLength: 255),
                    new OA\Property(
                        property: "term_ids",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 5, 12],
                        nullable: true
                    ),
                ]
            )
        ),
        tags: ["Rooms"],
        parameters: [
            new OA\Parameter(
                name: "room",
                description: "ID номера",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 12)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные номера сохранены",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Данные номера сохранены"),
                        new OA\Property(
                            property: "data",
                            description: "Те же поля, что у GET /rooms/{room}",
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
    public function manageUpdate(): void
    {
    }

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/rooms/{room}/publish",
        description: "Доступно админу базы. Аналог кнопки «Опубликовать»: status = publish. Номер становится видимым в поиске и на карточке отеля.",
        summary: "Опубликовать номер",
        security: [['bearerAuth' => []]],
        tags: ["Rooms"],
        parameters: [
            new OA\Parameter(
                name: "room",
                description: "ID номера",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 12)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Номер опубликован",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Номер опубликован"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "status", "status_label"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 12),
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "publish",
                                    enum: ["publish", "draft"]
                                ),
                                new OA\Property(
                                    property: "status_label",
                                    type: "string",
                                    example: "Опубликован"
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
        ]
    )]
    public function publish(): void
    {
    }

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/rooms/{room}/hide",
        description: "Доступно админу базы. Аналог кнопки «Скрыть»: status = draft. Номер скрывается из публичного поиска и карточки отеля.",
        summary: "Скрыть номер",
        security: [['bearerAuth' => []]],
        tags: ["Rooms"],
        parameters: [
            new OA\Parameter(
                name: "room",
                description: "ID номера",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 12)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Номер скрыт",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Номер скрыт"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "status", "status_label"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 12),
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "draft",
                                    enum: ["publish", "draft"]
                                ),
                                new OA\Property(
                                    property: "status_label",
                                    type: "string",
                                    example: "Черновик"
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
        ]
    )]
    public function hide(): void
    {
    }

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/rooms/{room}",
        description: "Доступно админу базы. Soft delete номера (deleted_at). Аналог кнопки «Удалить» на странице управления номерами.",
        summary: "Удалить номер",
        security: [['bearerAuth' => []]],
        tags: ["Rooms"],
        parameters: [
            new OA\Parameter(
                name: "room",
                description: "ID номера",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 12)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Номер удалён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Номер удалён"),
                        new OA\Property(
                            property: "data",
                            required: ["id"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 12),
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
    public function destroy(): void
    {
    }
}
