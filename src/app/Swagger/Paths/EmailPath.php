<?php

namespace App\Swagger\Paths;

use App\Swagger\ApiConfig;
use OpenApi\Attributes as OA;

class EmailPath
{
    #[OA\Post(
        path: '/api/' . ApiConfig::VERSION . '/support',
        summary: 'Отправить сообщение из формы поддержки',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'message'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Иван'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com'),
                    new OA\Property(property: 'message', type: 'string', example: 'Подскажите по бронированию.'),
                ],
                type: 'object'
            )
        ),
        tags: ['Email'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Письмо успешно отправлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Письмо успешно отправлено.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                ref: '#/components/responses/ValidationError',
                response: 422
            ),
        ]
    )]
    public function sendSupportMessage(): void
    {
    }
}
