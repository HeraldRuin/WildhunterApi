<?php
return [
    'name' => [
        'model_name' => 'Отели',
    ],
    'errors' => [
        'hotel_not_found' => 'Отель не найден',
        'max_booking_days' => 'Максимальный срок бронирования — 30 дней',
        'min_day_stays' => 'Необходимо забронировать минимум на :number дней',
        'min_day_before_booking' => 'Бронирование возможно минимум за :number дней',
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

        'hotel_id_required' => 'Укажите отель',
        'hotel_id_must_be_integer' => 'Поле "отель" должно быть числом',

        'check_in_required' => 'Укажите дату заезда',
        'check_in_must_be_date' => 'Дата заезда должна быть корректной датой',

        'check_out_required' => 'Укажите дату выезда',
        'check_out_must_be_date' => 'Дата выезда должна быть корректной датой',
        'check_out_must_be_after_check_in' => 'Дата выезда должна быть позже даты заезда',

        'adults_required' => 'Укажите количество взрослых',
        'adults_must_be_integer' => 'Количество взрослых должно быть числом',
        'adults_min_value' => 'Минимальное количество взрослых — 1',

        'hunter_data_must_be_date' => 'Дата охоты должна быть корректной датой',
        'hunters_must_be_integer' => 'Количество охотников должно быть числом',
        'hunters_min_value' => 'Минимальное количество охотников — 1',

        'children_must_be_integer' => 'Количество детей должно быть числом',
        'children_min_value' => 'Минимальное количество детей — 0',

        'star_rate_must_be_array' => 'Поле "рейтинг" должно быть массивом',
        'star_rate_item_must_be_string' => 'Значение рейтинга должно быть строкой',
        'star_rate_invalid' => 'Выбранный рейтинг недоступен',

        'term_ids_must_be_array' => 'Поле "term_ids" должно быть массивом.',
        'term_id_must_be_integer' => 'Каждый идентификатор термина должен быть целым числом.',
        'term_id_not_exists' => 'Один или несколько указанных терминов не существуют.',

        'price_must_be_array' => 'Поле "цена" должно быть массивом',
        'price_min_must_be_numeric' => 'Минимальная цена должна быть числом',
        'price_max_must_be_numeric' => 'Максимальная цена должна быть числом',
        'price_must_be_positive' => 'Цена не может быть отрицательной',
        'price_max_must_be_greater_than_min' => 'Максимальная цена должна быть больше или равна минимальной',
    ],
    'successes' => [

    ]
];
