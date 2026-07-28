<?php
return [
    'name' => [
        'model_name' => 'Оружие',
    ],
    'errors' => [
        'weapon_not_found' => 'Оружие не найдено',
    ],
    'rules' => [

    ],
    'validation' => [
        'hunter_billet_number_required' => 'Номер охотничьего билета обязателен, если не переданы данные оружия',
        'hunter_billet_number_string' => 'Номер охотничьего билета должен быть строкой',
        'hunter_billet_number_max' => 'Номер охотничьего билета не должен превышать 255 символов',

        'hunter_license_number_required' => 'Номер лицензии обязателен',
        'hunter_license_number_string' => 'Номер лицензии должен быть строкой',

        'hunter_license_date_required' => 'Дата лицензии обязательна',
        'hunter_license_date_invalid' => 'Неверная дата лицензии',

        'weapon_type_required' => 'Тип оружия обязателен',
        'weapon_type_not_found' => 'Тип оружия не найден',

        'caliber_required' => 'Калибр обязателен',
        'caliber_integer' => 'Калибр должен быть числом',
        'caliber_not_found' => 'Калибр не найден',
    ],
    'successes' => [
        'save_success' => 'Лицензия на оружие сохранена',
        'update_success' => 'Лицензия на оружие обновлена',
        'delete_success' => 'Оружие удалено.',
    ]
];
