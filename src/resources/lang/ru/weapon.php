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
        'hunter_billet_number_required' => 'Номер охотничьего билета обязателен',
        'hunter_billet_number_string' => 'Номер охотничьего билета должен быть строкой',
        'hunter_billet_number_max' => 'Номер охотничьего билета не должен превышать 255 символов',

        'hunter_billet_issuing_authority_required' => 'Исполнительный орган обязателен',
        'hunter_billet_issuing_authority_string' => 'Исполнительный орган должен быть строкой',
        'hunter_billet_issuing_authority_max' => 'Исполнительный орган не должен превышать 255 символов',

        'hunter_billet_rf_subject_required' => 'Субъект РФ обязателен',
        'hunter_billet_rf_subject_string' => 'Субъект РФ должен быть строкой',
        'hunter_billet_rf_subject_max' => 'Субъект РФ не должен превышать 255 символов',

        'hunter_billet_issue_date_required' => 'Дата выдачи охотничьего билета обязательна',
        'hunter_billet_issue_date_invalid' => 'Неверная дата выдачи охотничьего билета',

        'hunter_license_number_required' => 'Лицензия обязательна',
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
