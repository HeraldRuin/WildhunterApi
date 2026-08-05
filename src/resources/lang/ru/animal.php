<?php

return [
    'errors' => [
        'hotel_not_found' => 'Отель не найден',
        'animal_not_found' => 'Животное не найдено',
        'animal_not_available_at_hotel' => 'Это животное недоступно в выбранном отеле',
        'hunt_date_out_of_stay' => 'Дата охоты должна быть в пределах дат проживания',
        'animal_unavailable_on_date' => 'На эту дату охота на это животное недоступна',
        'price_period_not_found' => 'На выбранную дату нет ценового периода',
        'min_hunters' => 'Минимальное количество охотников для этого животного: :min. Вы выбрали: :selected',
    ],
    'validation' => [
        'hotel_id_required' => 'Укажите отель',
        'hotel_id_must_be_integer' => 'Поле "отель" должно быть числом',
        'animal_id_required' => 'Укажите животное',
        'animal_id_must_be_integer' => 'Поле "животное" должно быть числом',
        'hunter_data_required' => 'Укажите дату охоты',
        'hunter_data_must_be_date' => 'Дата охоты должна быть корректной датой',
        'hunters_required' => 'Укажите количество охотников',
        'hunters_must_be_integer' => 'Количество охотников должно быть числом',
        'hunters_min_value' => 'Минимальное количество охотников — 1',
        'check_in_must_be_date' => 'Дата заезда должна быть корректной датой',
        'check_out_must_be_date' => 'Дата выезда должна быть корректной датой',
        'check_out_must_be_after_check_in' => 'Дата выезда должна быть позже даты заезда',
    ],
];
