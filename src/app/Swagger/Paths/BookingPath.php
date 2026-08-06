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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
                ref: "#/components/responses/SuccessResponse",
                response: 201
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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
                ref: "#/components/responses/SuccessResponse",
                response: 200
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
