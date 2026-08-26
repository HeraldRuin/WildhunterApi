<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class AnimalsPath
{
    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/animals",
        summary: "Получить список животных",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список животных",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
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
    public function getAnimals(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/animals/check-availability",
        summary: "Проверка доступности животного на базе",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hotel_id", "animal_id", "hunter_data", "hunters"],
                properties: [
                    new OA\Property(
                        property: "hotel_id",
                        description: "ID отеля",
                        type: "integer",
                        example: 27,
                        minimum: 1
                    ),
                    new OA\Property(
                        property: "animal_id",
                        description: "ID животного",
                        type: "integer",
                        example: 5,
                        minimum: 1
                    ),
                    new OA\Property(
                        property: "hunter_data",
                        description: "Дата охоты",
                        type: "string",
                        format: "date",
                        example: "2026-06-24"
                    ),
                    new OA\Property(
                        property: "hunters",
                        description: "Количество взрослых охотников",
                        type: "integer",
                        example: 1,
                        minimum: 1
                    ),
                    new OA\Property(
                        property: "check_in",
                        description: "Дата заезда (опционально, для проверки что дата охоты в пределах проживания)",
                        type: "string",
                        format: "date",
                        example: "2026-06-24",
                        nullable: true
                    ),
                    new OA\Property(
                        property: "check_out",
                        description: "Дата выезда (опционально, для проверки что дата охоты в пределах проживания)",
                        type: "string",
                        format: "date",
                        example: "2026-06-25",
                        nullable: true
                    ),
                ]
            )
        ),
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Доступность и цена охоты",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["available", "price"],
                            properties: [
                                new OA\Property(property: "available", type: "boolean", example: true),
                                new OA\Property(property: "price", type: "number", format: "float"),
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
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/animals/manage",
        description: "Доступно админу базы. Возвращает животных, привязанных к отелю, и каталог доступных для добавления.",
        summary: "Управление животными базы",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список управляемых животных и каталог",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            required: ["animals", "available"],
                            properties: [
                                new OA\Property(
                                    property: "animals",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "title", "hunters_count"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "title", type: "string", example: "Косуля европейская"),
                                            new OA\Property(property: "hunters_count", type: "integer", example: 2),
                                        ],
                                        type: "object"
                                    )
                                ),
                                new OA\Property(
                                    property: "available",
                                    description: "Животные каталога, ещё не привязанные к базе (для селекта)",
                                    type: "array",
                                    items: new OA\Items(
                                        required: ["id", "title"],
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 5),
                                            new OA\Property(property: "title", type: "string", example: "Медведь бурый"),
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
    public function getManage(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/animals/manage",
        description: "Привязывает животное из каталога к отелю админа базы (как select на странице управления).",
        summary: "Добавить животное к базе",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["animal_id"],
                properties: [
                    new OA\Property(property: "animal_id", type: "integer", example: 5, minimum: 1),
                ]
            )
        ),
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Животное добавлено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Животное добавлено"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "title", "hunters_count"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 5),
                                new OA\Property(property: "title", type: "string", example: "Медведь бурый"),
                                new OA\Property(property: "hunters_count", type: "integer", example: 1),
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
    public function attachManage(): void
    {}

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/animals/manage/{animal}/hunters-count",
        description: "Обновляет hunters_count в bc_hotel_animals для животного базы.",
        summary: "Сохранить минимальное количество охотников",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["hunters_count"],
                properties: [
                    new OA\Property(property: "hunters_count", type: "integer", example: 2, minimum: 1),
                ]
            )
        ),
        tags: ["Animals"],
        parameters: [
            new OA\Parameter(
                name: "animal",
                description: "ID животного",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Количество охотников сохранено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Количество охотников сохранено"),
                        new OA\Property(
                            property: "data",
                            required: ["id", "title", "hunters_count"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "title", type: "string", example: "Косуля европейская"),
                                new OA\Property(property: "hunters_count", type: "integer", example: 2),
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
    public function updateManageHuntersCount(): void
    {}

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/animals/manage/{animal}",
        description: "Отвязывает животное от отеля админа базы (detach).",
        summary: "Удалить животное с базы",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
        parameters: [
            new OA\Parameter(
                name: "animal",
                description: "ID животного",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Животное удалено",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Животное удалено"),
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
        ]
    )]
    public function detachManage(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/animals/organisation",
        description: "Доступно админу базы. Возвращает животных, привязанных к отелю пользователя, с периодами стоимости.",
        summary: "Организация охоты: животные базы с ценовыми периодами",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список животных с периодами",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "periods"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "title", type: "string", example: "Косуля европейская"),
                                    new OA\Property(
                                        property: "periods",
                                        type: "array",
                                        items: new OA\Items(
                                            required: ["id", "start_date", "end_date", "price"],
                                            properties: [
                                                new OA\Property(property: "id", type: "integer", example: 10),
                                                new OA\Property(property: "start_date", type: "string", format: "date", nullable: true, example: "2025-12-23"),
                                                new OA\Property(property: "end_date", type: "string", format: "date", nullable: true, example: "2026-04-30"),
                                                new OA\Property(property: "price", type: "number", format: "float", nullable: true, example: 2500),
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
        ]
    )]
    public function getOrganisation(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/animals/{animal}/periods",
        description: "Создаёт пустой период (даты и цена заполняются через PUT). Доступно админу базы.",
        summary: "Добавить ценовой период животного",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
        parameters: [
            new OA\Parameter(
                name: "animal",
                description: "ID животного",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Период создан",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Период сохранен"),
                        new OA\Property(
                            property: "data",
                            required: ["animal_id", "period"],
                            properties: [
                                new OA\Property(property: "animal_id", type: "integer", example: 1),
                                new OA\Property(
                                    property: "period",
                                    required: ["id", "start_date", "end_date", "price"],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 11),
                                        new OA\Property(property: "start_date", type: "string", format: "date", nullable: true, example: null),
                                        new OA\Property(property: "end_date", type: "string", format: "date", nullable: true, example: null),
                                        new OA\Property(property: "price", type: "number", format: "float", nullable: true, example: null),
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
        ]
    )]
    public function createPeriod(): void
    {}

    #[OA\Put(
        path: "/api/" . ApiConfig::VERSION . "/animals/periods/{period}",
        description: "Обновляет даты и стоимость периода. Поле стоимости — amount (как в booking_core).",
        summary: "Сохранить ценовой период",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["start_date", "end_date", "amount"],
                properties: [
                    new OA\Property(property: "start_date", type: "string", format: "date", example: "2025-12-23"),
                    new OA\Property(property: "end_date", type: "string", format: "date", example: "2026-04-30"),
                    new OA\Property(property: "amount", type: "number", format: "float", example: 2500, minimum: 0),
                ]
            )
        ),
        tags: ["Animals"],
        parameters: [
            new OA\Parameter(
                name: "period",
                description: "ID периода",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Период обновлён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Период обновлен"),
                        new OA\Property(
                            property: "data",
                            required: ["period"],
                            properties: [
                                new OA\Property(
                                    property: "period",
                                    required: ["id", "start_date", "end_date", "price"],
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 10),
                                        new OA\Property(property: "start_date", type: "string", format: "date", example: "2025-12-23"),
                                        new OA\Property(property: "end_date", type: "string", format: "date", example: "2026-04-30"),
                                        new OA\Property(property: "price", type: "number", format: "float", example: 2500),
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
    public function updatePeriod(): void
    {}

    #[OA\Delete(
        path: "/api/" . ApiConfig::VERSION . "/animals/periods/{period}",
        summary: "Удалить ценовой период",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
        parameters: [
            new OA\Parameter(
                name: "period",
                description: "ID периода",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Период удалён",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Период удален"),
                        new OA\Property(
                            property: "data",
                            required: ["id"],
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 10),
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
    public function deletePeriod(): void
    {}

    #[OA\Get(
        path: "/api/" . ApiConfig::VERSION . "/animals/trophy-cost",
        description: "Доступно админу базы. Возвращает животных отеля с типами трофеев/штрафов/разделки и ценами для этой базы.",
        summary: "Стоимость трофея: животные базы с трофеями, штрафами и разделкой",
        security: [['bearerAuth' => []]],
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список животных со стоимостью",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: ""),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                required: ["id", "title", "trophies", "fines", "preparations"],
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "title", type: "string", example: "Косуля европейская"),
                                    new OA\Property(
                                        property: "trophies",
                                        type: "array",
                                        items: new OA\Items(
                                            required: ["id", "type", "price"],
                                            properties: [
                                                new OA\Property(property: "id", type: "integer", example: 8),
                                                new OA\Property(property: "type", type: "string", example: "1 рог"),
                                                new OA\Property(property: "price", type: "number", format: "float", nullable: true, example: 123),
                                            ],
                                            type: "object"
                                        )
                                    ),
                                    new OA\Property(
                                        property: "fines",
                                        type: "array",
                                        items: new OA\Items(
                                            required: ["id", "type", "price"],
                                            properties: [
                                                new OA\Property(property: "id", type: "integer", example: 2),
                                                new OA\Property(property: "type", type: "string", example: "Промах"),
                                                new OA\Property(property: "price", type: "number", format: "float", nullable: true, example: null),
                                            ],
                                            type: "object"
                                        )
                                    ),
                                    new OA\Property(
                                        property: "preparations",
                                        type: "array",
                                        items: new OA\Items(
                                            required: ["id", "type", "price"],
                                            properties: [
                                                new OA\Property(property: "id", type: "integer", example: 1),
                                                new OA\Property(property: "type", type: "string", example: "Разделка"),
                                                new OA\Property(property: "price", type: "number", format: "float", nullable: true, example: 5577),
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
        ]
    )]
    public function getTrophyCost(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/animals/trophy-cost/trophies",
        description: "Обновляет цену типа трофея для отеля админа базы. type должен быть trophies.",
        summary: "Сохранить стоимость трофея",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "id"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["trophies"], example: "trophies"),
                    new OA\Property(property: "id", type: "integer", example: 8),
                    new OA\Property(property: "price", type: "number", format: "float", nullable: true, minimum: 0, example: 123),
                ]
            )
        ),
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Цена сохранена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Трофей сохранен"),
                        new OA\Property(property: "data", type: "object", example: []),
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
    public function updateTrophyCost(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/animals/trophy-cost/fines",
        description: "Обновляет цену типа штрафа для отеля админа базы. type должен быть fines.",
        summary: "Сохранить стоимость штрафа",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "id"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["fines"], example: "fines"),
                    new OA\Property(property: "id", type: "integer", example: 2),
                    new OA\Property(property: "price", type: "number", format: "float", nullable: true, minimum: 0, example: 500),
                ]
            )
        ),
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Цена сохранена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Штраф сохранен"),
                        new OA\Property(property: "data", type: "object", example: []),
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
    public function updateFineCost(): void
    {}

    #[OA\Post(
        path: "/api/" . ApiConfig::VERSION . "/animals/trophy-cost/preparations",
        description: "Обновляет цену типа разделки для отеля админа базы. type должен быть preparations.",
        summary: "Сохранить стоимость разделки",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "id"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["preparations"], example: "preparations"),
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "price", type: "number", format: "float", nullable: true, minimum: 0, example: 5577),
                ]
            )
        ),
        tags: ["Animals"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Цена сохранена",
                content: new OA\JsonContent(
                    required: ["success", "message", "data"],
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Разделка сохранена"),
                        new OA\Property(property: "data", type: "object", example: []),
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
    public function updatePreparationCost(): void
    {}
}
