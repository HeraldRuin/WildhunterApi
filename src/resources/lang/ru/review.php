<?php
return [
    'name' => [
        'model_name' => 'Отзывы',
    ],
    'errors' => [

    ],
    'rules' => [

    ],
    'validation' => [
        'type_required' => 'Тип сервиса обязателен',
        'type_string' => 'Тип сервиса должен быть строкой',

        'order_by_must_be_string' => 'Поле "сортировка" должно быть строкой',

        'order_direction_must_be_string' => 'Направление сортировки должно быть строкой',
        'order_direction_invalid' => 'Направление сортировки может быть только asc или desc',

        'limit_must_be_numeric' => 'Параметр limit должен быть числом',
        'limit_min_value' => 'Минимальное значение limit — 1',
    ],
    'rate_text' => [
        'excellent' => 'Превосходный',
        'very_good' => 'Очень хороший',
        'average' => 'Хороший',
        'poor' => 'Плохой',
        'terrible' => 'Ужасный',
        'not_rated' => 'Без рейтинга',
    ],

    'successes' => [
        'save_success' => 'Отзыв сохранен.',
    ]
];
