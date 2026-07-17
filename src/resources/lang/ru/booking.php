<?php
return [
    'errors' => [
        'unknown_booking_type' => 'Неизвестный тип бронирования',
        'cannot_select_more_than_one_place' => 'Нельзя выбрать больше одного места',
        'no_free_places_in_room' => 'В этой комнате свободных мест нет',
        'cancel_only_own_place' => 'Вы можете отменить только своё занятое место',
        'hunter_already_in_booking' => 'Такой охотник уже есть в списке этого бронирования',
        'user_not_found' => 'Такой охотник не найден',
        'master_not_found' => 'Мастер охотник не найден',
        'booking_not_confirmable' => 'Эта бронь уже подтверждена или недоступна для подтверждения',
        'booking_cannot_be_cancelled' => 'Эта бронь уже отменена или завершена и не доступна для отмены',
        'booking_access_denied' => 'Доступ запрещён',
        'booking_status_locked' => 'Статус бронирования заблокирован',
        'booking_invitation_not_found' => 'Для этой брони не найден приглашенный',
        'not_all_hunters_confirmed' => 'Не все приглашённые участники подтвердили приглашение. Дождитесь ответа всех участников',
        'booking_hunter_gathering_not_started' => 'Сбор охотников не начат',
    ],
    'validation' => [
        'hotel_id_required' => 'Укажите отель',
        'hotel_id_must_be_integer' => 'ID отеля должен быть числом',
        'hotel_id_not_found' => 'Отель не найден',

        'animal_id_must_be_integer' => 'ID животного должен быть числом',
        'animal_id_not_found' => 'Животное не найдено',

        'check_in_required' => 'Укажите дату заезда',
        'check_in_must_be_date' => 'Дата заезда должна быть корректной датой',
        'check_in_must_be_today_or_later' => 'Дата заезда не может быть в прошлом',

        'check_out_required' => 'Укажите дату выезда',
        'check_out_must_be_date' => 'Дата выезда должна быть корректной датой',
        'check_out_must_be_after_check_in' => 'Дата выезда должна быть позже даты заезда',

        'adults_must_be_integer' => 'Количество взрослых должно быть числом',
        'adults_min_value' => 'Минимальное количество взрослых — 1',

        'hunters_must_be_integer' => 'Количество охотников должно быть числом',
        'hunters_min_value' => 'Минимальное количество охотников — 1',

        'rooms_required' => 'Выберите хотя бы один номер',
        'rooms_must_be_array' => 'Список номеров должен быть массивом',
        'rooms_min_value' => 'Выберите хотя бы один номер',

        'room_id_required' => 'Укажите ID номера',
        'room_id_must_be_integer' => 'ID номера должен быть числом',
        'room_id_not_found' => 'Номер не найден или не принадлежит выбранному отелю',

        'room_number_required' => 'Укажите количество номеров',
        'room_number_must_be_integer' => 'Количество номеров должно быть числом',
        'room_number_min_value' => 'Минимальное количество номеров — 1',
    ],
    'successes' => [
        'place_selected' => 'Выбранное место занято за вами',
        'place_cancelled' => 'Выбранное место освобожденно',
        'hunter_removed' => 'Охотник успешно удален с этой охоты',
        'hunter_replace' => 'Охотник успешно заменён',
        'booking_cancelled' => 'Бронь успешно отменена',
        'booking_completed' => 'Бронь успешно завершена',
        'invitation_declined' => 'Приглашение отклонено',
        'invitation_accepted' => 'Приглашение принято',
        'customer_changed' => 'Заказчик изменён',
        'gathering_has_started' => 'Сбор охотников начат',
        'gathering_has_completed' => 'Сбор охотников завершён',
        'booking_confirmed' => 'Бронь успешно подтверждена',
        'hunter_gathering_cancelled' => 'Сбор охотников для этой брони отменён',
        'booking_invitation_sent' => 'Приглашение отправлено',
    ]
];
