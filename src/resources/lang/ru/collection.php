<?php

return [
    'errors' => [
        'booking_collection_not_startable' => 'Сбор можно запустить только для подтверждённой брони',
        'booking_collection_not_extendable' => 'Продлить можно только активный сбор охотников',
        'collection_timer_not_found' => 'Таймер сбора охотников не найден',
        'collection_timer_not_expired' => 'Таймер сбора охотников ещё не истёк',
        'timer_settings_access_denied' => 'Недостаточно прав для управления настройками таймеров',
        'hotel_required' => 'Для настроек таймеров необходим отель',
    ],
    'validation' => [
        'timer_type_required' => 'Укажите тип таймера',
        'timer_type_invalid' => 'Недопустимый тип таймера',
        'timer_hours_required' => 'Укажите размер таймера в часах',
        'timer_hours_must_be_integer' => 'Размер таймера должен быть целым числом',
        'timer_hours_min_value' => 'Минимальный размер таймера — 1 час',
    ],
    'successes' => [
        'gathering_has_started' => 'Сбор охотников начат',
        'gathering_has_extended' => 'Сбор охотников продлён',
        'timer_settings_saved' => 'Настройки таймера успешно сохранены',
    ],
];
