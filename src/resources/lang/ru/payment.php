<?php

return [
    'errors' => [
        'booking_prepayment_collection_not_active' => 'Сбор предоплаты не активен',
        'prepayment_invitation_not_accepted' => 'Для оплаты необходимо принять приглашение',
        'prepayment_timer_not_found' => 'Таймер сбора предоплаты не найден',
        'prepayment_timer_expired' => 'Срок внесения предоплаты истёк',
        'prepayment_marked_unpaid' => 'Срок оплаты для этого приглашения завершён',
        'payment_already_paid' => 'Предоплата уже внесена',
        'payment_not_found' => 'Платёж для этой брони не найден',
        'payment_gateway_error' => 'Платёжный сервис временно недоступен',
        'payment_gateway_not_configured' => 'Платёжный сервис не настроен',
        'payment_gateway_token_error' => 'Не удалось авторизоваться в платёжном сервисе',
        'payment_gateway_invalid_response' => 'Платёжный сервис вернул некорректный ответ',
        'payment_gateway_rejected' => 'Платёжный сервис отклонил запрос',
    ],
];
