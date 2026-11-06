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
                        description: "Колонка сортировки (в ListHotel — поле order)",
                        type: "string",
                        example: "id"
                    ),
                    new OA\Property(
                        property: "order_direction",
                        type: "string",
                        example: "desc",
                        enum: ["asc", "desc"],
                        description: "Направление (в ListHotel — поле order_by)"
                    ),
                    new OA\Property(
                        property: "limit",
                        description: "Лимит (в ListHotel — number), по умолчанию 5",
                        type: "integer",
                        example: 5
                    ),
                    new OA\Property(
                        property: "is_featured",
                        description: "Только featured-базы (как «Only featured items?»)",
                        type: "boolean",
                        example: true
                    ),
                    new OA\Property(
                        property: "custom_ids",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [27, 30],
                        description: "Явный список ID в нужном порядке (List by IDs)"
                    ),
                    new OA\Property(
                        property: "location_id",
                        type: "integer",
                        example: 12,
                        description: "Фильтр по локации (включая вложенные)"
                    ),
                    new OA\Property(
                        property: "all",
                        type: "boolean",
                        example: true,
                        description: "Все опубликованные базы, без лимита и списка с главной"
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
                                required: ["id", "title", "slug", "map_lat", "map_lng", "image_url", "star_rate", "has_food", "is_featured", "price", "review_count", "location"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "title", type: "string", nullable: true),
                                    new OA\Property(property: "slug", type: "string", nullable: true),
                                    new OA\Property(property: "map_lat", type: "string", nullable: true),
                                    new OA\Property(property: "map_lng", type: "string", nullable: true),
                                    new OA\Property(property: "image_url", type: "string"),
                                    new OA\Property(property: "star_rate", type: "integer", nullable: true),
                                    new OA\Property(property: "has_food", type: "boolean"),
                                    new OA\Property(property: "is_featured", type: "boolean"),
                                    new OA\Property(property: "price", type: "number", format: "float", nullable: true),
                                    new OA\Property(property: "review_count", type: "integer"),
                                    new OA\Property(
                                        property: "hunting_methods",
                                        type: "array",
                                        items: new OA\Items(
                                            required: ["id", "title"],
                                            properties: [
                                                new OA\Property(property: "id", type: "integer", example: 1),
                                                new OA\Property(property: "title", type: "string", example: "Загонная"),
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
                            required: ["id", "title", "slug", "address", "content", "map_lat", "map_lng", "image_url", "gallery", "review_count", "star_rate", "rooms", "animals", "location"],
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "title", type: "string", nullable: true),
                                new OA\Property(property: "slug", type: "string", nullable: true),
                                new OA\Property(property: "address", type: "string", nullable: true),
                                new OA\Property(property: "content", type: "string", nullable: true),
                                new OA\Property(property: "map_lat", type: "string", nullable: true),
                                new OA\Property(property: "map_lng", type: "string", nullable: true),
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
                                new OA\Property(property: "check_in_time", type: "string", example: "10:00", nullable: true),
                                new OA\Property(property: "check_out_time", type: "string", example: "12:00", nullable: true),
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
                                        required: ["id", "title", "slug", "image_url", "content", "hunters_count", "max_hunters_count", "hunt_type", "periods"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "title", type: "string", nullable: true),
                                            new OA\Property(property: "slug", type: "string", nullable: true),
                                            new OA\Property(property: "image_url", type: "string"),
                                            new OA\Property(property: "content", type: "string", nullable: true),
                                            new OA\Property(property: "hunters_count", type: "integer", example: 2),
                                            new OA\Property(property: "max_hunters_count", type: "integer", example: 8),
                                            new OA\Property(
                                                property: "hunt_type",
                                                description: "Тип охоты животного: индивидуальная или групповая",
                                                required: ["code", "title"],
                                                properties: [
                                                    new OA\Property(
                                                        property: "code",
                                                        type: "string",
                                                        enum: ["individual", "group"],
                                                        example: "group"
                                                    ),
                                                    new OA\Property(property: "title", type: "string", example: "Групповая"),
                                                ],
                                                type: "object"
                                            ),
                                            new OA\Property(
                                                property: "periods",
                                                description: "Ценовые периоды животного с текущего месяца (прошедшие месяцы не отдаются)",
                                                type: "array",
                                                items: new OA\Items(
                                                    required: ["id", "start_date", "end_date", "price"],
                                                    properties: [
                                                        new OA\Property(property: "id", type: "integer", example: 10),
                                                        new OA\Property(property: "start_date", type: "string", format: "date", example: "2026-09-01", nullable: true),
                                                        new OA\Property(property: "end_date", type: "string", format: "date", example: "2026-11-30", nullable: true),
                                                        new OA\Property(property: "price", type: "number", format: "float", nullable: true, example: 2500),
                                                    ],
                                                    type: "object"
                                                )
                                            ),
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
                                new OA\Property(
                                    property: "terms",
                                    description: "Услуги конкретной базы. Пустой массив — на фронте показывается заглушка",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "title", "terms"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "title", type: "string", example: "Услуги на базе"),
                                            new OA\Property(
                                                property: "terms",
                                                type: "array",
                                                items: new OA\Items(
                                                    required: ["id", "title"],
                                                    properties: [
                                                        new OA\Property(property: "id", type: "integer", example: 10),
                                                        new OA\Property(property: "title", type: "string", example: "Wi-Fi"),
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
                        description: "ID локации из формы поиска (иерархический фильтр, включая вложенные)",
                        type: "integer",
                        example: 1,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "location_ids",
                        description: "Массив ID локаций из сайдбара (OR: отель попадает, если его location_id в списке). При одновременной передаче с location_id применяется пересечение (AND)",
                        type: "array",
                        items: new OA\Items(type: "integer", example: 1),
                        example: [1, 3, 5],
                        nullable: true
                    ),
                    new OA\Property(
                        property: "animal_id",
                        description: "ID животного из формы поиска (dropdown «Дичь»)",
                        type: "integer",
                        example: 5,
                        nullable: true
                    ),
                    new OA\Property(
                        property: "animal_ids",
                        description: "Массив ID животных из сайдбара (OR: база попадает, если у неё есть хотя бы одно из выбранных). При одновременной передаче с animal_id применяется пересечение (AND)",
                        type: "array",
                        items: new OA\Items(type: "integer", example: 2),
                        example: [2, 7, 12],
                        nullable: true
                    ),
                    new OA\Property(
                        property: "hunting_method_ids",
                        description: "Массив ID способов охоты из сайдбара (OR: база попадает, если у неё есть хотя бы один из выбранных). Пустой массив или отсутствие поля — не фильтровать",
                        type: "array",
                        items: new OA\Items(type: "integer", example: 1),
                        example: [1, 3],
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
                        property: "sort",
                        description: "Сортировка результатов поиска",
                        type: "string",
                        example: "recommended",
                        nullable: true,
                        enum: ["recommended", "price_asc", "price_desc", "rating"]
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
                                                property: "hunting_methods",
                                                type: "array",
                                                items: new OA\Items(
                                                    required: ["id", "title"],
                                                    properties: [
                                                        new OA\Property(property: "id", type: "integer", example: 1),
                                                        new OA\Property(property: "title", type: "string", example: "Загонная"),
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

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/hotels/manage",
        description: "Доступно админу базы. Создаёт новую базу и привязывает её к текущему пользователю (admin_base). Обязательно только title. Если slug не передан — генерируется из названия. Статус по умолчанию — draft. В ответе — данные базы в формате формы редактирования.",
        summary: "Создать базу",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Хромой кабан-2", maxLength: 255),
                    new OA\Property(property: "slug", type: "string", example: "khromoi-kaban-2", nullable: true, maxLength: 255),
                    new OA\Property(property: "content", type: "string", nullable: true),
                    new OA\Property(property: "star_rate", type: "integer", example: 4, nullable: true, maximum: 5, minimum: 0),
                    new OA\Property(property: "address", type: "string", nullable: true, maxLength: 255),
                    new OA\Property(property: "image_id", type: "integer", example: 120, nullable: true),
                    new OA\Property(
                        property: "gallery",
                        description: "Массив ID файлов из media_files",
                        type: "array",
                        items: new OA\Items(type: "integer", example: 121),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "policy",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "surrounding",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(property: "price", type: "number", format: "float", example: 4000, nullable: true, minimum: 0),
                    new OA\Property(property: "check_in_time", type: "string", example: "10:00", nullable: true, maxLength: 255),
                    new OA\Property(property: "check_out_time", type: "string", example: "11:00", nullable: true, maxLength: 255),
                    new OA\Property(property: "min_day_before_booking", type: "integer", example: 3, nullable: true, minimum: 0),
                    new OA\Property(property: "min_day_stays", type: "integer", example: 2, nullable: true, minimum: 0),
                    new OA\Property(
                        property: "extra_price",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "service_fee",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(property: "map_lat", type: "string", nullable: true),
                    new OA\Property(property: "map_lng", type: "string", nullable: true),
                    new OA\Property(property: "location_id", type: "integer", example: 1, nullable: true),
                    new OA\Property(
                        property: "status",
                        type: "string",
                        example: "draft",
                        nullable: true,
                        enum: ["publish", "draft", "pending"]
                    ),
                    new OA\Property(property: "has_food", type: "boolean", example: false, nullable: true),
                    new OA\Property(
                        property: "term_ids",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 5, 12],
                        nullable: true
                    ),
                    new OA\Property(
                        property: "hunting_method_ids",
                        description: "ID способов охоты из GET /hotels/hunting-methods. У одной базы может быть несколько способов",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 3],
                        nullable: true
                    ),
                    new OA\Property(
                        property: "max_hunts_per_day",
                        description: "Количество егерей на базе",
                        type: "integer",
                        example: 2,
                        nullable: true,
                        minimum: 0
                    ),
                ]
            )
        ),
        tags: ["Hotels"],
        responses: [
            new OA\Response(
                response: 200,
                description: "База создана",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "База создана"),
                        new OA\Property(
                            property: "data",
                            description: "Те же поля, что у GET /hotels/manage/{hotel}",
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
                description: "Нет прав baseAdmin"
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
        path: "/api/" . ApiConfig::VERSION . "/hotels/manage/{hotel}",
        description: "Доступно админу базы. Возвращает данные своей базы для формы редактирования (контент, политика, места, ценообразование, атрибуты). Аналог открытия базы на странице «Управление базой».",
        summary: "Данные базы для редактирования",
        security: [['bearerAuth' => []]],
        tags: ["Hotels"],
        parameters: [
            new OA\Parameter(
                name: "hotel",
                description: "ID базы",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 27)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные базы для редактирования",
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
                                "slug",
                                "content",
                                "star_rate",
                                "address",
                                "image_id",
                                "image_url",
                                "gallery",
                                "policy",
                                "surrounding",
                                "price",
                                "check_in_time",
                                "check_out_time",
                                "min_day_before_booking",
                                "min_day_stays",
                                "extra_price",
                                "service_fee",
                                "map_lat",
                                "map_lng",
                                "location_id",
                                "location",
                                "status",
                                "status_label",
                                "has_food",
                                "term_ids",
                                "hunting_method_ids",
                            ],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 27),
                                new OA\Property(property: "title", type: "string", example: "Хромой кабан-2", nullable: true),
                                new OA\Property(property: "slug", type: "string", example: "khromoi-kaban-2", nullable: true),
                                new OA\Property(property: "content", type: "string", nullable: true),
                                new OA\Property(property: "star_rate", type: "integer", example: 4, nullable: true),
                                new OA\Property(property: "address", type: "string", nullable: true),
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
                                new OA\Property(
                                    property: "policy",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "surrounding",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(property: "price", type: "number", format: "float", example: 4000, nullable: true),
                                new OA\Property(property: "check_in_time", type: "string", example: "10:00", nullable: true),
                                new OA\Property(property: "check_out_time", type: "string", example: "11:00", nullable: true),
                                new OA\Property(property: "min_day_before_booking", type: "integer", example: 3, nullable: true),
                                new OA\Property(property: "min_day_stays", type: "integer", example: 2, nullable: true),
                                new OA\Property(
                                    property: "extra_price",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "service_fee",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(property: "map_lat", type: "string", nullable: true),
                                new OA\Property(property: "map_lng", type: "string", nullable: true),
                                new OA\Property(property: "location_id", type: "integer", example: 1, nullable: true),
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
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "publish",
                                    enum: ["publish", "draft", "pending", "trash"]
                                ),
                                new OA\Property(property: "status_label", type: "string", example: "Опубликован"),
                                new OA\Property(property: "has_food", type: "boolean", example: false),
                                new OA\Property(
                                    property: "term_ids",
                                    type: "array",
                                    items: new OA\Items(type: "integer"),
                                    example: [1, 5, 12]
                                ),
                                new OA\Property(
                                    property: "hunting_method_ids",
                                    type: "array",
                                    items: new OA\Items(type: "integer"),
                                    example: [1, 3]
                                ),
                                new OA\Property(
                                    property: "max_hunts_per_day",
                                    description: "Количество егерей на базе",
                                    type: "integer",
                                    example: 2
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
                description: "Нет прав baseAdmin"
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
        path: "/api/" . ApiConfig::VERSION . "/hotels/manage/{hotel}",
        description: "Доступно админу базы. Сохраняет данные своей базы из формы редактирования (контент, политика, места, ценообразование, атрибуты). В ответе — актуальные данные базы в том же формате, что и GET.",
        summary: "Сохранить базу",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Хромой кабан-2", maxLength: 255),
                    new OA\Property(property: "slug", type: "string", example: "khromoi-kaban-2", nullable: true, maxLength: 255),
                    new OA\Property(property: "content", type: "string", nullable: true),
                    new OA\Property(property: "star_rate", type: "integer", example: 4, nullable: true, maximum: 5, minimum: 0),
                    new OA\Property(property: "address", type: "string", nullable: true, maxLength: 255),
                    new OA\Property(property: "image_id", type: "integer", example: 120, nullable: true),
                    new OA\Property(
                        property: "gallery",
                        description: "Массив ID файлов из media_files",
                        type: "array",
                        items: new OA\Items(type: "integer", example: 121),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "policy",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "surrounding",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(property: "price", type: "number", format: "float", example: 4000, nullable: true, minimum: 0),
                    new OA\Property(property: "check_in_time", type: "string", example: "10:00", nullable: true, maxLength: 255),
                    new OA\Property(property: "check_out_time", type: "string", example: "11:00", nullable: true, maxLength: 255),
                    new OA\Property(property: "min_day_before_booking", type: "integer", example: 3, nullable: true, minimum: 0),
                    new OA\Property(property: "min_day_stays", type: "integer", example: 2, nullable: true, minimum: 0),
                    new OA\Property(
                        property: "extra_price",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(
                        property: "service_fee",
                        type: "array",
                        items: new OA\Items(type: "object"),
                        nullable: true
                    ),
                    new OA\Property(property: "map_lat", type: "string", nullable: true),
                    new OA\Property(property: "map_lng", type: "string", nullable: true),
                    new OA\Property(property: "location_id", type: "integer", example: 1, nullable: true),
                    new OA\Property(
                        property: "status",
                        type: "string",
                        example: "publish",
                        nullable: true,
                        enum: ["publish", "draft", "pending"]
                    ),
                    new OA\Property(property: "has_food", type: "boolean", example: false, nullable: true),
                    new OA\Property(
                        property: "term_ids",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 5, 12],
                        nullable: true
                    ),
                    new OA\Property(
                        property: "hunting_method_ids",
                        description: "ID способов охоты из GET /hotels/hunting-methods. У одной базы может быть несколько способов",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 3],
                        nullable: true
                    ),
                    new OA\Property(
                        property: "max_hunts_per_day",
                        description: "Количество егерей на базе",
                        type: "integer",
                        example: 2,
                        nullable: true,
                        minimum: 0
                    ),
                ]
            )
        ),
        tags: ["Hotels"],
        parameters: [
            new OA\Parameter(
                name: "hotel",
                description: "ID базы",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 27)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Данные сохранены",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Данные сохранены"),
                        new OA\Property(
                            property: "data",
                            required: [
                                "id",
                                "title",
                                "slug",
                                "content",
                                "star_rate",
                                "address",
                                "image_id",
                                "image_url",
                                "gallery",
                                "policy",
                                "surrounding",
                                "price",
                                "check_in_time",
                                "check_out_time",
                                "min_day_before_booking",
                                "min_day_stays",
                                "extra_price",
                                "service_fee",
                                "map_lat",
                                "map_lng",
                                "location_id",
                                "location",
                                "status",
                                "status_label",
                                "has_food",
                                "term_ids",
                                "hunting_method_ids",
                            ],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 27),
                                new OA\Property(property: "title", type: "string", example: "Хромой кабан-2", nullable: true),
                                new OA\Property(property: "slug", type: "string", example: "khromoi-kaban-2", nullable: true),
                                new OA\Property(property: "content", type: "string", nullable: true),
                                new OA\Property(property: "star_rate", type: "integer", example: 4, nullable: true),
                                new OA\Property(property: "address", type: "string", nullable: true),
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
                                new OA\Property(
                                    property: "policy",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "surrounding",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(property: "price", type: "number", format: "float", example: 4000, nullable: true),
                                new OA\Property(property: "check_in_time", type: "string", example: "10:00", nullable: true),
                                new OA\Property(property: "check_out_time", type: "string", example: "11:00", nullable: true),
                                new OA\Property(property: "min_day_before_booking", type: "integer", example: 3, nullable: true),
                                new OA\Property(property: "min_day_stays", type: "integer", example: 2, nullable: true),
                                new OA\Property(
                                    property: "extra_price",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: "service_fee",
                                    type: "array",
                                    items: new OA\Items(type: "object"),
                                    nullable: true
                                ),
                                new OA\Property(property: "map_lat", type: "string", nullable: true),
                                new OA\Property(property: "map_lng", type: "string", nullable: true),
                                new OA\Property(property: "location_id", type: "integer", example: 1, nullable: true),
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
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    example: "publish",
                                    enum: ["publish", "draft", "pending", "trash"]
                                ),
                                new OA\Property(property: "status_label", type: "string", example: "Опубликован"),
                                new OA\Property(property: "has_food", type: "boolean", example: false),
                                new OA\Property(
                                    property: "term_ids",
                                    type: "array",
                                    items: new OA\Items(type: "integer"),
                                    example: [1, 5, 12]
                                ),
                                new OA\Property(
                                    property: "hunting_method_ids",
                                    type: "array",
                                    items: new OA\Items(type: "integer"),
                                    example: [1, 3]
                                ),
                                new OA\Property(
                                    property: "max_hunts_per_day",
                                    description: "Количество егерей на базе",
                                    type: "integer",
                                    example: 2
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
                description: "Нет прав baseAdmin"
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

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/hotels/manage/{hotel}",
        description: "Доступно админу базы. Открепляет текущего администратора от базы: поле admin_base обнуляется, сама запись отеля не удаляется. Аналог кнопки «Удалить» на странице «Управление базой».",
        summary: "Открепить базу от администратора",
        security: [['bearerAuth' => []]],
        tags: ["Hotels"],
        parameters: [
            new OA\Parameter(
                name: "hotel",
                description: "ID базы",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 27)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Администратор откреплён от базы",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "База удалена из управления"),
                        new OA\Property(
                            property: "data",
                            required: ["id"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 27),
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
                description: "Нет прав baseAdmin"
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

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/hotels/hunting-methods",
        summary: "Получить способы охоты",
        tags: ["Hotels"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список способов охоты",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "name", "slug"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string"),
                                    new OA\Property(property: "slug", type: "string"),
                                ],
                                type: "object"
                            )
                        ),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function GetHuntingMethods(): void
    {
    }

}
