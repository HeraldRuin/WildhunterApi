<?php
return [
    'name' => [
        'model_name' => 'Локации',
    ],
    'errors' => [
        'location_not_found' => 'Локация не найдена',
    ],
    'rules' => [

    ],
    'validation' => [
        'order_by_must_be_string' => 'Поле "сортировка" должно быть строкой',

        'order_direction_must_be_string' => 'Направление сортировки должно быть строкой',
        'order_direction_invalid' => 'Направление сортировки может быть только asc или desc',

        'limit_must_be_numeric' => 'Параметр limit должен быть числом',
        'limit_min_value' => 'Минимальное значение limit — 1',
    ],
    'successes' => [

    ]
];
