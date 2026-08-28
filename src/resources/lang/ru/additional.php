<?php

return [
    'errors' => [
        'additional_not_found' => 'Услуга не найдена',
        'additional_cannot_delete' => 'Эту услугу удалить нельзя',
        'services_access_denied' => 'Нет доступа к разделу услуг',
        'hotel_required' => 'Чтобы управлять этим разделом нужно сначала создать базу',
    ],
    'validation' => [
        'name_required' => 'Поле «название» обязательно для заполнения',
        'name_must_be_string' => 'Название должно быть строкой',
        'name_max' => 'Название не должно превышать 255 символов',
        'price_required' => 'Поле «цена» обязательно для заполнения',
        'price_must_be_numeric' => 'Цена должна быть числом',
        'price_min' => 'Цена не может быть отрицательной',
        'count_must_be_integer' => 'Количество должно быть целым числом',
        'count_min' => 'Количество не может быть отрицательным',
        'calculation_type_required' => 'Поле «тип расчёта» обязательно для заполнения',
        'calculation_type_invalid' => 'Некорректный тип расчёта',
    ],
    'successes' => [
        'additional_saved' => 'Услуга сохранена',
        'additional_updated' => 'Услуга обновлена',
        'additional_deleted' => 'Услуга удалена',
    ],
];
