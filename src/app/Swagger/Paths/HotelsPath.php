<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class HotelsPath
{
    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/hotels/offers",
        summary: "Лучшие предложения отелей",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "order_by",
                        type: "string",
                        example: "created_at"
                    ),
                    new OA\Property(
                        property: "order_direction",
                        type: "string",
                        example: "desc",
                        enum: ["asc", "desc"]
                    ),
                    new OA\Property(
                        property: "limit",
                        type: "integer",
                        example: "3"
                    ),
                ]
            )
        ),
        tags: ["Hotels"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список отелей",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "slug", "map_lat", "map_lng", "image_url", "star_rate", "price", "review_count", "location"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                    new OA\Property(property: "map_lat", type: "string", nullable: true),
                                    new OA\Property(property: "map_lng", type: "string", nullable: true),
                                    new OA\Property(property: "image_url", type: "string"),
                                    new OA\Property(property: "star_rate", type: "integer", nullable: true),
                                    new OA\Property(property: "price", type: "number", format: "float", nullable: true),
                                    new OA\Property(property: "review_count", type: "integer"),
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
                                ],
                                type: "object"
                            )
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
    public function offers(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/hotels/{location}/{slug}",
        summary: "Получить информацию об отеле",
        security: [['bearerAuth' => []]],
        tags: ["Hotels"],
        parameters: [
            new OA\Parameter(
                name: "slug",
                description: "Slug отеля",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "dva-olenia"
                )
            ),
            new OA\Parameter(
                name: "location",
                description: "локация отеля",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "string",
                    example: "moskovskaia-oblast",
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Подробные данные отеля",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["id", "title", "slug", "address", "content", "image_url", "gallery", "review_count", "star_rate", "rooms", "animals", "location"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "title", type: "string", nullable: true),
                                new OA\Property(property: "slug", type: "string", nullable: true),
                                new OA\Property(property: "address", type: "string", nullable: true),
                                new OA\Property(property: "content", type: "string", nullable: true),
                                new OA\Property(property: "image_url", type: "string"),
                                new OA\Property(
                                    property: "gallery",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["large", "medium", "thumb"],
                                        properties: [
                                            new OA\Property(property: "large", type: "string"),
                                            new OA\Property(property: "medium", type: "string"),
                                            new OA\Property(property: "thumb", type: "string"),
                                        ],
                                        type: "object"
                                    )
                                ),
                                new OA\Property(property: "review_count", type: "integer"),
                                new OA\Property(property: "star_rate", type: "integer", nullable: true),
                                new OA\Property(
                                    property: "rooms",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "title", "price", "nights", "size", "beds", "adults", "children", "number_selected", "number", "image_url", "gallery"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "title", type: "string", nullable: true),
                                            new OA\Property(property: "price", type: "number", format: "float", nullable: true),
                                            new OA\Property(property: "nights", type: "integer"),
                                            new OA\Property(property: "size", type: "string", nullable: true),
                                            new OA\Property(property: "beds", type: "integer", nullable: true),
                                            new OA\Property(property: "adults", type: "integer", nullable: true),
                                            new OA\Property(property: "children", type: "integer", nullable: true),
                                            new OA\Property(property: "number_selected", type: "integer"),
                                            new OA\Property(property: "number", type: "integer", nullable: true),
                                            new OA\Property(property: "image_url", type: "string"),
                                            new OA\Property(
                                                property: "gallery",
                                                type: "array",
                                                items: new OA\Items(
                                                    required: ["large", "medium", "thumb"],
                                                    properties: [
                                                        new OA\Property(property: "large", type: "string"),
                                                        new OA\Property(property: "medium", type: "string"),
                                                        new OA\Property(property: "thumb", type: "string"),
                                                    ],
                                                    type: "object"
                                                )
                                            ),
                                        ],
                                        type: "object"
                                    )
                                ),
                                new OA\Property(
                                    property: "animals",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "title", "slug", "image_url", "content"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "title", type: "string", nullable: true),
                                            new OA\Property(property: "slug", type: "string", nullable: true),
                                            new OA\Property(property: "image_url", type: "string"),
                                            new OA\Property(property: "content", type: "string", nullable: true),
                                        ],
                                        type: "object"
                                    )
                                ),
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
        ]
    )]
    public function show(): void
    {
    }

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/hotels/search",
        summary: "Поиск отелей",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["check_in", "check_out"],
                properties: [
                    new OA\Property(
                        property: "location_id",
                        description: "ID локации",
                        type: "integer",
                        example: 1,
                        nullable: true
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
                        property: "price",
                        description: "Диапазон цены",
                        properties: [
                            new OA\Property(
                                property: "min",
                                description: "Минимальная цена",
                                type: "number",
                                format: "float",
                                example: 3000
                            ),
                            new OA\Property(
                                property: "max",
                                description: "Максимальная цена",
                                type: "number",
                                format: "float",
                                example: 10000
                            ),
                        ],
                        type: "object",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "star_rate",
                        description: "Рейтинг",
                        type: "array",
                        items: new OA\Items(
                            type: "string",
                            example: "excellent"
                        ),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "term_ids",
                        description: "Массив ID терминов (атрибутов) для фильтрации отелей",
                        type: "array",
                        items: new OA\Items(
                            type: "integer",
                            example: 12
                        ),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "order_by",
                        type: "string",
                        example: "created_at",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "order_direction",
                        type: "string",
                        example: "desc",
                        nullable: true,
                        enum: ["asc", "desc"]
                    ),
                    new OA\Property(
                        property: "limit",
                        description: "Количество элементов на странице. Если не передано, используется настройка hotel_page_limit_item или 9",
                        type: "integer",
                        example: 10,
                        nullable: true
                    ),
                ]
            )
        ),
        tags: ["Hotels"],
        parameters: [
            new OA\Parameter(
                name: "page",
                description: "Номер страницы. Если не передан, возвращается первая страница",
                in: "query",
                required: false,
                schema: new OA\Schema(
                    type: "integer",
                    example: 1,
                    minimum: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Результаты поиска отелей",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["items", "pagination"],
                            properties: [
                                new OA\Property(
                                    property: "items",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "title", "slug", "map_lat", "map_lng", "image_url", "price", "review_count", "star_rate", "has_food", "is_in_wishList", "location"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "title", type: "string", nullable: true),
                                            new OA\Property(property: "slug", type: "string", nullable: true),
                                            new OA\Property(property: "map_lat", type: "string", nullable: true),
                                            new OA\Property(property: "map_lng", type: "string", nullable: true),
                                            new OA\Property(property: "image_url", type: "string"),
                                            new OA\Property(property: "price", type: "number", format: "float", nullable: true),
                                            new OA\Property(property: "review_count", type: "integer"),
                                            new OA\Property(property: "star_rate", type: "integer", nullable: true),
                                            new OA\Property(property: "has_food", type: "boolean"),
                                            new OA\Property(property: "is_in_wishList", type: "boolean"),
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
                                        ],
                                        type: "object"
                                    )
                                ),
                                new OA\Property(
                                    property: "pagination",
                                    required: ["current_page", "per_page", "total", "last_page", "has_more_pages"],
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
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ValidationError",
                response: 422
            ),
        ]
    )]
    public function search(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/hotels/manage",
        description: "Доступно админу базы. Возвращает все отели (базы), привязанные к текущему пользователю через admin_base. Аналог страницы «Управление базой».",
        summary: "Список баз администратора",
        security: [['bearerAuth' => []]],
        tags: ["Hotels"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список баз",
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
                                    "title",
                                    "slug",
                                    "image_url",
                                    "price",
                                    "status",
                                    "status_label",
                                    "updated_at",
                                    "location",
                                ],
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 27),
                                    new OA\Property(property: "title", type: "string", example: "Хромой кабан-2"),
                                    new OA\Property(property: "slug", type: "string", example: "khromoi-kaban-2"),
                                    new OA\Property(property: "image_url", type: "string"),
                                    new OA\Property(property: "price", type: "number", format: "float", example: 4000),
                                    new OA\Property(
                                        property: "status",
                                        type: "string",
                                        example: "publish",
                                        enum: ["publish", "draft", "pending", "trash"]
                                    ),
                                    new OA\Property(property: "status_label", type: "string", example: "Опубликован"),
                                    new OA\Property(
                                        property: "updated_at",
                                        type: "string",
                                        format: "date-time",
                                        example: "2026-08-04 09:45:00"
                                    ),
                                    new OA\Property(
                                        property: "location",
                                        required: ["id", "name", "slug"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "name", type: "string", example: "Ярославская область"),
                                            new OA\Property(property: "slug", type: "string", example: "iaroslavskaia-oblast"),
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
            new OA\Response(
                ref: "#/components/responses/AuthResponse",
                response: 401
            ),
            new OA\Response(
                response: 403,
                description: "Нет прав baseAdmin"
            ),
        ]
    )]
    public function manageList(): void
    {
    }

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/hotels/price-range",
        summary: "Получить минимальную и максимальную стоимость отелей",
        security: [['bearerAuth' => []]],
        tags: ["Hotels"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Минимальная и максимальная цена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["min_price", "max_price"],
                            properties: [
                                new OA\Property(property: "min_price", type: "number", format: "float"),
                                new OA\Property(property: "max_price", type: "number", format: "float"),
                            ],
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function priceRange(): void
    {
    }

}
