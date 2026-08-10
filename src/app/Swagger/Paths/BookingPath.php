<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class BookingPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/bookings/history",
        summary: "История бронирований (охотник / администратор базы)",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
        parameters: [
            new OA\Parameter(
                name: "status",
                description: "Фильтр статуса. Для вкладки приглашений охотника: invitation",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", example: "invitation")
            ),
            new OA\Parameter(
                name: "code",
                description: "Код бронирования из ссылки-приглашения",
                in: "query",
                required: false,
                schema: new OA\Schema(
                    type: "string",
                    example: "faa1c65d4b0de02146a27cea429340fb"
                )
            ),
            new OA\Parameter(
                name: "booking_id",
                description: "Фильтр по ID брони",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", example: 222)
            ),
            new OA\Parameter(
                name: "page",
                description: "Страница пагинации",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "История бронирований",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["role", "hotel", "statuses", "dropdown_statuses", "bookings"],
                            properties: [
                                new OA\Property(
                                    property: "role",
                                    type: "string",
                                    enum: ["baseadmin", "hunter"]
                                ),
                                new OA\Property(
                                    property: "hotel",
                                    required: ["id", "title", "slug", "location"],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer"),
                                        new OA\Property(property: "title", type: "string", nullable: true),
                                        new OA\Property(property: "slug", type: "string", nullable: true),
                                        new OA\Property(
                                            property: "location",
                                            required: ["slug"],
                                            properties: [
                                                new OA\Property(property: "slug", type: "string", nullable: true),
                                            ],
                                            type: "object",
                                            nullable: true
                                        ),
                                    ],
                                    type: "object",
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "statuses",
                                    type: "array",
                                    items: new OA\Items(type: "string")
                                ),
                                new OA\Property(
                                    property: "dropdown_statuses",
                                    type: "array",
                                    items: new OA\Items(type: "string")
                                ),
                                new OA\Property(
                                    property: "bookings",
                                    required: ["items", "pagination"],
                                    properties: [
                                        new OA\Property(
                                            property: "items",
                                            type: "array",
                                            items: new OA\Items(
                                                required: [
                                                    "id", "booking_number", "code", "created_at", "type",
                                                    "type_text", "status", "status_for_user", "status_label",
                                                    "display_status", "is_paid", "is_master_hunter",
                                                    "is_invited", "invitation_accepted", "invitation_url",
                                                    "hotel", "creator", "details", "collection", "payment",
                                                    "available_actions",
                                                ],
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "booking_number", type: "string"),
                                                    new OA\Property(property: "code", type: "string"),
                                                    new OA\Property(property: "created_at", type: "string", format: "date-time"),
                                                    new OA\Property(
                                                        property: "type",
                                                        type: "string",
                                                        enum: ["hotel", "animal", "hotel_animal"]
                                                    ),
                                                    new OA\Property(property: "type_text", type: "string"),
                                                    new OA\Property(property: "status", type: "string"),
                                                    new OA\Property(property: "status_for_user", type: "string"),
                                                    new OA\Property(property: "status_label", type: "string"),
                                                    new OA\Property(property: "display_status", type: "string"),
                                                    new OA\Property(property: "is_paid", type: "boolean"),
                                                    new OA\Property(property: "is_master_hunter", type: "boolean"),
                                                    new OA\Property(property: "is_invited", type: "boolean"),
                                                    new OA\Property(property: "invitation_accepted", type: "boolean"),
                                                    new OA\Property(property: "invitation_url", type: "string"),
                                                    new OA\Property(
                                                        property: "hotel",
                                                        required: [
                                                            "id", "title", "slug", "location",
                                                            "collection_timer_hours", "paid_timer_hours",
                                                            "bed_timer_hours",
                                                        ],
                                                        properties: [
                                                            new OA\Property(property: "id", type: "integer"),
                                                            new OA\Property(property: "title", type: "string", nullable: true),
                                                            new OA\Property(property: "slug", type: "string", nullable: true),
                                                            new OA\Property(
                                                                property: "location",
                                                                required: ["slug"],
                                                                properties: [
                                                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                                                ],
                                                                type: "object",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "collection_timer_hours",
                                                                type: "integer",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "paid_timer_hours",
                                                                type: "integer",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "bed_timer_hours",
                                                                type: "integer",
                                                                nullable: true
                                                            ),
                                                        ],
                                                        type: "object",
                                                        nullable: true
                                                    ),
                                                    new OA\Property(
                                                        property: "creator",
                                                        required: [
                                                            "id", "user_name", "first_name",
                                                            "last_name", "email", "phone",
                                                        ],
                                                        properties: [
                                                            new OA\Property(property: "id", type: "integer"),
                                                            new OA\Property(property: "user_name", type: "string", nullable: true),
                                                            new OA\Property(property: "first_name", type: "string", nullable: true),
                                                            new OA\Property(property: "last_name", type: "string", nullable: true),
                                                            new OA\Property(
                                                                property: "email",
                                                                type: "string",
                                                                format: "email",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(property: "phone", type: "string", nullable: true),
                                                        ],
                                                        type: "object",
                                                        nullable: true
                                                    ),
                                                    new OA\Property(
                                                        property: "details",
                                                        required: [
                                                            "start_date", "end_date", "duration_days",
                                                            "total_guests", "start_date_animal",
                                                            "total_hunting", "animal", "rooms",
                                                        ],
                                                        properties: [
                                                            new OA\Property(
                                                                property: "start_date",
                                                                type: "string",
                                                                format: "date"
                                                            ),
                                                            new OA\Property(
                                                                property: "end_date",
                                                                type: "string",
                                                                format: "date"
                                                            ),
                                                            new OA\Property(property: "duration_days", type: "integer"),
                                                            new OA\Property(property: "total_guests", type: "integer"),
                                                            new OA\Property(
                                                                property: "start_date_animal",
                                                                type: "string",
                                                                format: "date",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "total_hunting",
                                                                type: "integer",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "animal",
                                                                required: ["id", "title"],
                                                                properties: [
                                                                    new OA\Property(property: "id", type: "integer"),
                                                                    new OA\Property(property: "title", type: "string", nullable: true),
                                                                ],
                                                                type: "object",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "rooms",
                                                                type: "array",
                                                                items: new OA\Items(
                                                                    required: [
                                                                        "room_id", "title", "number",
                                                                        "price", "adults",
                                                                    ],
                                                                    properties: [
                                                                        new OA\Property(property: "room_id", type: "integer"),
                                                                        new OA\Property(
                                                                            property: "title",
                                                                            type: "string",
                                                                            nullable: true
                                                                        ),
                                                                        new OA\Property(property: "number", type: "integer"),
                                                                        new OA\Property(
                                                                            property: "price",
                                                                            type: "number",
                                                                            format: "float"
                                                                        ),
                                                                        new OA\Property(property: "adults", type: "integer"),
                                                                    ],
                                                                    type: "object"
                                                                )
                                                            ),
                                                        ],
                                                        type: "object"
                                                    ),
                                                    new OA\Property(
                                                        property: "collection",
                                                        required: [
                                                            "accepted_count", "total_needed", "paid_count", "invitations",
                                                            "collection_end_at", "paid_end_at", "beds_end_at",
                                                        ],
                                                        properties: [
                                                            new OA\Property(property: "accepted_count", type: "integer"),
                                                            new OA\Property(property: "total_needed", type: "integer"),
                                                            new OA\Property(property: "paid_count", type: "integer"),
                                                            new OA\Property(
                                                                property: "invitations",
                                                                type: "array",
                                                                items: new OA\Items(
                                                                    required: [
                                                                        "invitation_id", "hunter_id", "user_name",
                                                                        "name", "email", "status", "is_accepted",
                                                                    ],
                                                                    properties: [
                                                                        new OA\Property(
                                                                            property: "invitation_id",
                                                                            type: "integer"
                                                                        ),
                                                                        new OA\Property(
                                                                            property: "hunter_id",
                                                                            type: "integer",
                                                                            nullable: true
                                                                        ),
                                                                        new OA\Property(
                                                                            property: "user_name",
                                                                            type: "string",
                                                                            nullable: true
                                                                        ),
                                                                        new OA\Property(
                                                                            property: "name",
                                                                            type: "string",
                                                                            nullable: true
                                                                        ),
                                                                        new OA\Property(
                                                                            property: "email",
                                                                            type: "string",
                                                                            format: "email",
                                                                            nullable: true
                                                                        ),
                                                                        new OA\Property(
                                                                            property: "status",
                                                                            type: "string",
                                                                            example: "Приглашение принято"
                                                                        ),
                                                                        new OA\Property(
                                                                            property: "is_accepted",
                                                                            type: "boolean"
                                                                        ),
                                                                    ],
                                                                    type: "object"
                                                                )
                                                            ),
                                                            new OA\Property(
                                                                property: "collection_end_at",
                                                                type: "string",
                                                                format: "date-time",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "paid_end_at",
                                                                type: "string",
                                                                format: "date-time",
                                                                nullable: true
                                                            ),
                                                            new OA\Property(
                                                                property: "beds_end_at",
                                                                type: "string",
                                                                format: "date-time",
                                                                nullable: true
                                                            ),
                                                        ],
                                                        type: "object"
                                                    ),
                                                    new OA\Property(
                                                        property: "payment",
                                                        required: ["prepaid_total", "base_total", "total"],
                                                        properties: [
                                                            new OA\Property(
                                                                property: "prepaid_total",
                                                                type: "number",
                                                                format: "float"
                                                            ),
                                                            new OA\Property(
                                                                property: "base_total",
                                                                type: "number",
                                                                format: "float"
                                                            ),
                                                            new OA\Property(
                                                                property: "total",
                                                                type: "number",
                                                                format: "float"
                                                            ),
                                                        ],
                                                        type: "object"
                                                    ),
                                                    new OA\Property(
                                                        property: "available_actions",
                                                        type: "array",
                                                        items: new OA\Items(
                                                            required: ["code", "label"],
                                                            properties: [
                                                                new OA\Property(property: "code", type: "string"),
                                                                new OA\Property(property: "label", type: "string"),
                                                            ],
                                                            type: "object"
                                                        )
                                                    ),
                                                ],
                                                type: "object"
                                            )
                                        ),
                                        new OA\Property(
                                            property: "pagination",
                                            required: [
                                                "current_page", "per_page", "total",
                                                "last_page", "has_more_pages",
                                            ],
                                            properties: [
                                                new OA\Property(property: "current_page", type: "integer"),
                                                new OA\Property(property: "per_page", type: "integer"),
                                                new OA\Property(property: "total", type: "integer"),
                                                new OA\Property(property: "last_page", type: "integer"),
                                                new OA\Property(property: "has_more_pages", type: "boolean"),
                                            ],
                                            type: "object"
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
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
        ]
    )]
    public function GetBookingHistory(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/confirm",
        summary: "Подтвердить бронь (администратор базы)",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
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
                description: "Бронирование подтверждено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "code", "status"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "status", type: "string"),
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
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
        ]
    )]
    public function ConfirmBooking(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/cancel-collection",
        summary: "Отменить активный сбор охотников",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
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
                description: "Сбор охотников отменён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Сбор охотников для этой брони отменён"
                        ),
                        new OA\Property(
                            property: "data",
                            required: ["id", "code", "status"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "status", type: "string", example: "confirmed"),
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
                description: "Текущий пользователь не является мастером охотником"
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                response: 409,
                description: "Сбор охотников не запущен"
            ),
        ]
    )]
    public function CancelBookingCollection(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/invite-hunter",
        summary: "Пригласить охотника в активный сбор",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hunter_id"],
                properties: [
                    new OA\Property(
                        property: "hunter_id",
                        description: "ID приглашаемого пользователя",
                        type: "integer",
                        example: 42
                    ),
                ]
            )
        ),
        tags: ["Bookings"],
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
                description: "Приглашение отправлено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Приглашение отправлено"),
                        new OA\Property(
                            property: "data",
                            required: ["invitation_id", "hunter_id", "status"],
                            properties: [
                                new OA\Property(property: "invitation_id", type: "integer", example: 123),
                                new OA\Property(property: "hunter_id", type: "integer", example: 42),
                                new OA\Property(property: "status", type: "string", example: "pending"),
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
                description: "Текущий пользователь не является мастером охотником"
            ),
            new OA\Response(
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                response: 409,
                description: "Сбор не запущен или охотник уже приглашён"
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
        ]
    )]
    public function InviteHunter(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/accept-invitation",
        summary: "Принять приглашение в бронь",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
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
                description: "Приглашение принято",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Приглашение принято"),
                        new OA\Property(
                            property: "data",
                            required: ["status", "accepted_at"],
                            properties: [
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "Приглашение принято"
                                ),
                                new OA\Property(
                                    property: "accepted_at",
                                    type: "string",
                                    format: "date-time"
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
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
        ]
    )]
    public function AcceptBookingInvitation(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/decline-invitation",
        summary: "Отклонить приглашение в бронь",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
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
                description: "Приглашение отклонено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Приглашение отклонено"),
                        new OA\Property(
                            property: "data",
                            required: ["status", "declined_at"],
                            properties: [
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "Приглашение отклонено"
                                ),
                                new OA\Property(
                                    property: "declined_at",
                                    type: "string",
                                    format: "date-time"
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
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
        ]
    )]
    public function DeclineBookingInvitation(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/cancel",
        summary: "Отменить бронь",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
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
                description: "Бронирование отменено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "code", "status"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "status", type: "string"),
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
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
        ]
    )]
    public function CancelBooking(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/change-user",
        summary: "Сменить заказчика бронирования (администратор базы)",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id"],
                properties: [
                    new OA\Property(
                        property: "user_id",
                        description: "ID нового заказчика",
                        type: "integer",
                        example: 15
                    ),
                ]
            )
        ),
        tags: ["Bookings"],
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
                description: "Заказчик изменён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Заказчик изменён"),
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
                description: "Пользователь не является администратором этой базы"
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
    public function ChangeBookingCustomer(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings",
        summary: "Создать бронирование",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hotel_id", "check_in", "check_out", "rooms"],
                properties: [
                    new OA\Property(
                        property: "hotel_id",
                        description: "ID отеля",
                        type: "integer",
                        example: 27
                    ),
                    new OA\Property(
                        property: "animal_id",
                        description: "ID животного",
                        type: "integer",
                        example: 5,
                        nullable: true
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
                        example: 1
                    ),
                    new OA\Property(
                        property: "hunters",
                        description: "Количество охотников",
                        type: "integer",
                        example: 1
                    ),
                    new OA\Property(
                        property: "rooms",
                        description: "Выбранные номера",
                        type: "array",
                        items: new OA\Items(
                            required: ["room_id", "number"],
                            properties: [
                                new OA\Property(
                                    property: "room_id",
                                    description: "ID номера",
                                    type: "integer",
                                    example: 10
                                ),
                                new OA\Property(
                                    property: "number",
                                    description: "Количество номеров",
                                    type: "integer",
                                    example: 1
                                ),
                            ],
                            type: "object"
                        )
                    ),
                ]
            )
        ),
        tags: ["Bookings"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Бронирование создано",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            required: ["booking_code"],
                            properties: [
                                new OA\Property(property: "booking_code", type: "string"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
        ]
    )]
    public function store(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/checkout",
        summary: "Получить данные бронирования перед подтверждением",
        security: [['bearerAuth' => []]],
        tags: ["Bookings"],
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
                description: "Данные бронирования перед подтверждением",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: [
                                "booking_number", "created_at", "status", "gateway", "type",
                                "check_in", "check_out", "start_date_animal", "location",
                                "hotel", "animal", "total", "amount_hunting", "all_total",
                                "deposit", "total_guests", "total_hunting", "rooms",
                            ],
                            properties: [
                                new OA\Property(property: "booking_number", type: "string", nullable: true),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
                                new OA\Property(property: "status", type: "string", nullable: true),
                                new OA\Property(property: "gateway", type: "string", nullable: true),
                                new OA\Property(property: "type", type: "string", nullable: true),
                                new OA\Property(property: "check_in", type: "string", format: "date", nullable: true),
                                new OA\Property(property: "check_out", type: "string", format: "date", nullable: true),
                                new OA\Property(property: "start_date_animal", type: "string", format: "date", nullable: true),
                                new OA\Property(
                                    property: "location",
                                    required: ["id", "name", "slug"],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer"),
                                        new OA\Property(property: "name", type: "string", nullable: true),
                                        new OA\Property(property: "slug", type: "string", nullable: true),
                                    ],
                                    type: "object",
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "hotel",
                                    required: ["id", "title", "slug", "image_url"],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer"),
                                        new OA\Property(property: "title", type: "string", nullable: true),
                                        new OA\Property(property: "slug", type: "string", nullable: true),
                                        new OA\Property(property: "image_url", type: "string"),
                                    ],
                                    type: "object",
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "animal",
                                    required: ["id", "title", "slug", "image_url", "content"],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer"),
                                        new OA\Property(property: "title", type: "string", nullable: true),
                                        new OA\Property(property: "slug", type: "string", nullable: true),
                                        new OA\Property(property: "image_url", type: "string"),
                                        new OA\Property(property: "content", type: "string", nullable: true),
                                    ],
                                    type: "object",
                                    nullable: true
                                ),
                                new OA\Property(property: "total", type: "number", format: "float"),
                                new OA\Property(property: "amount_hunting", type: "number", format: "float"),
                                new OA\Property(property: "all_total", type: "number", format: "float"),
                                new OA\Property(property: "deposit", type: "number", format: "float"),
                                new OA\Property(property: "total_guests", type: "integer", nullable: true),
                                new OA\Property(property: "total_hunting", type: "integer", nullable: true),
                                new OA\Property(
                                    property: "rooms",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["room_id", "title", "number", "price"],
                                        properties: [
                                            new OA\Property(property: "room_id", type: "integer"),
                                            new OA\Property(property: "title", type: "string", nullable: true),
                                            new OA\Property(property: "number", type: "integer"),
                                            new OA\Property(property: "price", type: "number", format: "float"),
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
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
        ]
    )]
    public function checkout(): void
    {}

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/bookings/customer-notes",
        summary: "Сохранить особые требования к бронированию",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["code", "customer_notes"],
                properties: [
                    new OA\Property(
                        property: "code",
                        description: "Код бронирования",
                        type: "string",
                        example: "faa1c65d4b0de02146a27cea429340fb"
                    ),
                    new OA\Property(
                        property: "customer_notes",
                        description: "Особые требования / комментарий к бронированию",
                        type: "string",
                        example: "Нужен поздний заезд"
                    ),
                ]
            )
        ),
        tags: ["Bookings"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Сохранённые особые требования",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            required: ["customer_notes"],
                            properties: [
                                new OA\Property(property: "customer_notes", type: "string", nullable: true),
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
                ref: "#/components/responses/NotFoundResponse",
                response: 404
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
        ]
    )]
    public function updateCustomerNotes(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/bookings/{code}/checkout",
        summary: "Подтвердить бронирование",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["term_conditions"],
                properties: [
                    new OA\Property(
                        property: "first_name",
                        description: "Имя",
                        type: "string",
                        example: "Иван"
                    ),
                    new OA\Property(
                        property: "last_name",
                        description: "Фамилия",
                        type: "string",
                        example: "Иванов"
                    ),
                    new OA\Property(
                        property: "email",
                        description: "Email",
                        type: "string",
                        format: "email",
                        example: "ivan@example.com"
                    ),
                    new OA\Property(
                        property: "phone",
                        description: "Телефон",
                        type: "string",
                        example: "+79001234567"
                    ),
                    new OA\Property(
                        property: "address_line_1",
                        description: "Адрес",
                        type: "string",
                        example: "ул. Ленина, 1",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "address_line_2",
                        description: "Дополнительный адрес",
                        type: "string",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "city",
                        description: "Город",
                        type: "string",
                        example: "Москва",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "state",
                        description: "Регион",
                        type: "string",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "zip_code",
                        description: "Почтовый индекс",
                        type: "string",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "country",
                        description: "Страна",
                        type: "string",
                        example: "RU",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "customer_notes",
                        description: "Комментарий к бронированию",
                        type: "string",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "how_to_pay",
                        description: "Способ оплаты",
                        type: "string",
                        enum: ["deposit", "full"],
                        example: "deposit",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "credit",
                        description: "Сумма списания с кошелька (в кредитах)",
                        type: "number",
                        format: "float",
                        example: 0,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "payment_gateway",
                        description: "Платёжный шлюз",
                        type: "string",
                        example: "offline_payment",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "term_conditions",
                        description: "Согласие с условиями бронирования",
                        type: "boolean",
                        example: true
                    ),
                ]
            )
        ),
        tags: ["Bookings"],
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
                response: 500,
                description: "Endpoint не реализован: метод BookingController::doCheckout отсутствует"
            ),
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
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
    public function doCheckout(): void
    {}
}
