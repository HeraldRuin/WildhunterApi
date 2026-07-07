<?php
return [
    'name' => [
        'model_name' => 'Отели',
    ],
    'errors' => [
        'hotel_not_found' => 'Отель не найден',
    ],
    'rules' => [

    ],
    'validation' => [
        'order_by_must_be_string' => 'Поле "сортировка" должно быть строкой',

        'order_direction_must_be_string' => 'Направление сортировки должно быть строкой',
        'order_direction_invalid' => 'Направление сортировки может быть только asc или desc',

        'limit_must_be_numeric' => 'Параметр limit должен быть числом',
        'limit_min_value' => 'Минимальное значение limit — 1',

        'location_id_must_be_integer' => 'Поле "локация" должно быть числом',
        'animal_id_must_be_integer' => 'Поле "животные" должно быть числом',

        'check_in_required' => 'Укажите дату заезда',
        'check_in_must_be_date' => 'Дата заезда должна быть корректной датой',

        'check_out_required' => 'Укажите дату выезда',
        'check_out_must_be_date' => 'Дата выезда должна быть корректной датой',
        'check_out_must_be_after_check_in' => 'Дата выезда должна быть позже даты заезда',

        'adults_must_be_integer' => 'Количество взрослых должно быть числом',
        'adults_min_value' => 'Минимальное количество взрослых — 1',

        'children_must_be_integer' => 'Количество детей должно быть числом',
        'children_min_value' => 'Минимальное количество детей — 0',

        'star_rate_must_be_array' => 'Поле "рейтинг" должно быть массивом',
        'star_rate_item_must_be_string' => 'Значение рейтинга должно быть строкой',
        'star_rate_invalid' => 'Выбранный рейтинг недоступен',
    ],
    'successes' => [

    ]
];
