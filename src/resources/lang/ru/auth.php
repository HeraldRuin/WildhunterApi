<?php
return [
    'errors' => [
        'auth_user_false' => 'Требуется повторная авторизация. Пожалуйста, войдите в систему заново',
        'auth_token_mismatch' => 'Время сессии истекло. Пожалуйста, войдите в систему заново',
        'auth_invalid_credentials' => 'Неверные учетные данные',
        'auth_token_error' => 'Не авторизован',
        'register_for_more_features' => 'Чтобы получить больше возможностей на нашем сайте, пройдите регистрацию',
    ],
    'labels' => [
        'guest' => 'Гость',
    ],
    'rules' => [
        'registration_disabled' => 'Регистрация отключена',
    ],
    'validation' => [
        'first_name_required' => 'Имя обязательно',
        'last_name_required'  => 'Фамилия обязательна',

        'role_required' => 'Роль обязательна',
        'role_invalid'  => 'Недопустимая роль',

        'phone_required' => 'Телефон обязателен',
        'phone_unique'   => 'Такой телефон уже существует',

        'email_required' => 'Email обязателен',
        'email_invalid'  => 'Неверный формат email',
        'email_max'      => 'Email не должен превышать 255 символов',
        'email_unique'   => 'Этот email уже занят',

        'password_required' => 'Пароль обязателен',
        'password_string'   => 'Пароль должен быть строкой',
        'password_min_login' => 'Пароль должен быть минимум 6 символов',
        'password_min'      => 'Пароль должен быть минимум 8 символов',
        'password_upper_lower' => 'Пароль должен содержать заглавные и строчные буквы',
        'password_number' => 'Пароль должен содержать хотя бы одну цифру',
        'password_compromised' => 'Этот пароль найден в утечках данных. Выберите другой пароль.',

        'term_required'  => 'Необходимо согласие с условиями',
        'term_accepted'  => 'Необходимо принять условия',
    ],
    'successes' => [
        'register_successfully' => 'Вы успешно зарегистрированы',
    ]
];
