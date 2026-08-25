<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class NotificationsPath
{
    #[OA\Get(
        path: '/api/' . ApiConfig::VERSION . '/notifications',
        summary: 'Список уведомлений текущего пользователя',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                description: 'Фильтр: all | unread | read',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'all', enum: ['all', 'unread', 'read'])
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Размер страницы (1–100)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 20, default: 20, maximum: 100, minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список уведомлений',
                content: new OA\JsonContent(
                    required: ['success', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: ''),
                        new OA\Property(
                            property: 'data',
                            required: ['unread_count', 'notifications', 'pagination'],
                            properties: [
                                new OA\Property(property: 'unread_count', type: 'integer', example: 2),
                                new OA\Property(
                                    property: 'notifications',
                                    type: 'array',
                                    items: new OA\Items(
                                        required: [
                                            'id', 'title', 'message', 'unread', 'created_at', 'time_ago',
                                        ],
                                        properties: [
                                            new OA\Property(
                                                property: 'id',
                                                type: 'string',
                                                format: 'uuid',
                                                example: '444411f4-a4ac-4049-a488-ece54ea112ee'
                                            ),
                                            new OA\Property(
                                                property: 'title',
                                                type: 'string',
                                                example: 'Новое бронирование'
                                            ),
                                            new OA\Property(
                                                property: 'message',
                                                type: 'string',
                                                example: 'Поступила заявка на базу «Хромой кабан-2»'
                                            ),
                                            new OA\Property(
                                                property: 'link',
                                                type: 'string',
                                                example: '/profile/bookings/1247',
                                                nullable: true
                                            ),
                                            new OA\Property(
                                                property: 'category',
                                                type: 'string',
                                                example: 'booking',
                                                nullable: true
                                            ),
                                            new OA\Property(
                                                property: 'entity_type',
                                                type: 'string',
                                                example: 'booking',
                                                nullable: true
                                            ),
                                            new OA\Property(
                                                property: 'entity_id',
                                                type: 'integer',
                                                example: 1247,
                                                nullable: true
                                            ),
                                            new OA\Property(
                                                property: 'event',
                                                type: 'string',
                                                example: 'BookingCreatedEvent',
                                                nullable: true
                                            ),
                                            new OA\Property(property: 'unread', type: 'boolean', example: true),
                                            new OA\Property(
                                                property: 'read_at',
                                                type: 'string',
                                                format: 'date-time',
                                                example: null,
                                                nullable: true
                                            ),
                                            new OA\Property(
                                                property: 'created_at',
                                                type: 'string',
                                                format: 'date-time',
                                                example: '2026-08-24T17:53:00+00:00'
                                            ),
                                            new OA\Property(
                                                property: 'time_ago',
                                                type: 'string',
                                                example: '10 мин. назад'
                                            ),
                                        ],
                                        type: 'object'
                                    )
                                ),
                                new OA\Property(
                                    property: 'pagination',
                                    required: ['current_page', 'last_page', 'per_page', 'total'],
                                    properties: [
                                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                        new OA\Property(property: 'last_page', type: 'integer', example: 1),
                                        new OA\Property(property: 'per_page', type: 'integer', example: 20),
                                        new OA\Property(property: 'total', type: 'integer', example: 3),
                                    ],
                                    type: 'object'
                                ),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                ref: '#/components/responses/AuthResponse',
                response: 401
            ),
            new OA\Response(
                ref: '#/components/responses/ValidationError',
                response: 422
            ),
        ]
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/api/' . ApiConfig::VERSION . '/notifications/unread-count',
        summary: 'Количество непрочитанных уведомлений (для badge)',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Счётчик непрочитанных',
                content: new OA\JsonContent(
                    required: ['success', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: ''),
                        new OA\Property(
                            property: 'data',
                            required: ['unread_count'],
                            properties: [
                                new OA\Property(property: 'unread_count', type: 'integer', example: 2),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                ref: '#/components/responses/AuthResponse',
                response: 401
            ),
        ]
    )]
    public function unreadCount(): void
    {
    }

    #[OA\Post(
        path: '/api/' . ApiConfig::VERSION . '/notifications/{notificationId}/read',
        summary: 'Отметить уведомление как прочитанное',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'notificationId',
                description: 'UUID уведомления',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid',
                    example: '444411f4-a4ac-4049-a488-ece54ea112ee'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Уведомление отмечено как прочитанное',
                content: new OA\JsonContent(
                    required: ['success', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Уведомление отмечено как прочитанное'
                        ),
                        new OA\Property(
                            property: 'data',
                            required: ['unread_count'],
                            properties: [
                                new OA\Property(property: 'unread_count', type: 'integer', example: 1),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                ref: '#/components/responses/AuthResponse',
                response: 401
            ),
        ]
    )]
    public function markAsRead(): void
    {
    }

    #[OA\Post(
        path: '/api/' . ApiConfig::VERSION . '/notifications/read-all',
        summary: 'Отметить все уведомления как прочитанные',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Все уведомления отмечены как прочитанные',
                content: new OA\JsonContent(
                    required: ['success', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Все уведомления отмечены как прочитанные'
                        ),
                        new OA\Property(
                            property: 'data',
                            required: ['unread_count'],
                            properties: [
                                new OA\Property(property: 'unread_count', type: 'integer', example: 0),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                ref: '#/components/responses/AuthResponse',
                response: 401
            ),
        ]
    )]
    public function markAllAsRead(): void
    {
    }
}
